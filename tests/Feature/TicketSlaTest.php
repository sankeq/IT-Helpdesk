<?php

namespace Tests\Feature;

use App\Enums\Impact;
use App\Enums\PendingReason;
use App\Enums\Priority;
use App\Enums\SlaEventType;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\BusinessCalendar;
use App\Models\Location;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\TicketSlaEvent;
use App\Models\User;
use App\Notifications\SlaBreachNotification;
use App\Notifications\SlaWarningNotification;
use App\Notifications\TicketAssignedNotification;
use App\Services\SlaCalculatorService;
use App\Services\TicketService;
use App\Services\TicketWorkflowService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TicketSlaTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $agent;

    private User $supervisor;

    private TicketCategory $category;

    private Location $location;

    private BusinessCalendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->category = TicketCategory::where('code', 'HW-PC')->first();
        $this->location = Location::where('code', 'MKS-HQ')->first();
        $this->calendar = BusinessCalendar::where('name', 'Jam Kerja PT IKI (Makassar WITA)')->first();
    }

    public function test_business_calendar_adds_minutes_respecting_work_hours_weekends_and_holidays(): void
    {
        // Friday 2026-10-16 16:00 WITA = 2026-10-16 08:00 UTC
        $start = Carbon::create(2026, 10, 16, 16, 0, 0, 'Asia/Makassar');

        // Add 120 minutes (2 business hours)
        // 16:00 to 17:00 Friday = 60 minutes
        // Saturday & Sunday are weekend -> skipped
        // Monday 2026-10-19 08:00 to 09:00 = remaining 60 minutes
        $result = $this->calendar->addBusinessMinutes($start, 120);

        $resultInWita = $result->copy()->setTimezone('Asia/Makassar');
        $this->assertEquals('2026-10-19 09:00:00', $resultInWita->format('Y-m-d H:i:s'));

        // Now test if Monday is a holiday
        $this->calendar->update(['holidays' => ['2026-10-19']]);
        $resultWithHoliday = $this->calendar->addBusinessMinutes($start, 120);
        $resultHolidayWita = $resultWithHoliday->copy()->setTimezone('Asia/Makassar');
        // Monday skipped, lands on Tuesday 2026-10-20 09:00:00
        $this->assertEquals('2026-10-20 09:00:00', $resultHolidayWita->format('Y-m-d H:i:s'));
    }

    public function test_ticket_creation_snapshots_sla_policy_and_preserves_historical_target(): void
    {
        // Fix test clock during Friday business hours: 2026-10-16 10:00 WITA
        Carbon::setTestNow(Carbon::create(2026, 10, 16, 10, 0, 0, 'Asia/Makassar'));

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->createTicket($this->employee, [
            'subject' => 'Printer Rusak',
            'description' => 'Tinta bocor dan macet.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'impact' => Impact::Medium->value,
            'urgency' => Urgency::High->value, // Matrix: Medium + High = P2
        ]);

        $this->assertEquals(Priority::P2, $ticket->priority);
        $this->assertNotNull($ticket->sla_policy_id);
        $this->assertNotNull($ticket->sla_policy_snapshot);
        $this->assertEquals('P2', $ticket->sla_policy_snapshot['priority']);
        $this->assertEquals(30, $ticket->sla_policy_snapshot['first_response_minutes']);
        $this->assertEquals(480, $ticket->sla_policy_snapshot['resolution_minutes']);

        // Check target dates:
        // Response due in 30 min -> 10:30 WITA
        $firstResponseWita = $ticket->first_response_due_at->timezone('Asia/Makassar')->format('Y-m-d H:i');
        $this->assertEquals('2026-10-16 10:30', $firstResponseWita);

        $initialResolutionDue = $ticket->resolution_due_at;

        // Mutate the original SLA Policy in database
        SlaPolicy::where('priority', 'P2')->update(['resolution_minutes' => 9999]);

        // Assert ticket snapshot and target remain immutable (T-013)
        $ticketFresh = $ticket->fresh();
        $this->assertEquals(480, $ticketFresh->sla_policy_snapshot['resolution_minutes']);
        $this->assertEquals($initialResolutionDue->toIso8601String(), $ticketFresh->resolution_due_at->toIso8601String());

        Carbon::setTestNow();
    }

    public function test_sla_pauses_on_vendor_pending_and_resumes_extending_due_date(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 16, 9, 0, 0, 'Asia/Makassar'));

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->createTicket($this->employee, [
            'subject' => 'Penggantian Motherboard PC',
            'description' => 'Motherboard terbakar.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'impact' => Impact::Medium->value,
            'urgency' => Urgency::Medium->value, // P3 (960 resolution minutes)
        ]);

        $initialDue = $ticket->resolution_due_at;
        $workflowService = app(TicketWorkflowService::class);

        // 1. Assign to agent
        $ticketService->assignTicket($ticket, $this->agent, $this->supervisor);

        // 2. Put in progress
        $workflowService->transition($ticket, TicketStatus::InProgress, $this->agent);

        // 3. Put to Pending (waiting_vendor) -> allowed pause reason
        $workflowService->transition($ticket, TicketStatus::Pending, $this->agent, [
            'pending_reason' => PendingReason::WaitingVendor->value,
            'pending_note' => 'Menunggu pengiriman komponen motherboard dari vendor resmi ASUS Makassar.',
        ]);

        $ticket->refresh();
        $this->assertTrue($ticket->is_sla_paused);
        $this->assertNotNull($ticket->last_paused_at);

        $pauseEvent = TicketSlaEvent::where('ticket_id', $ticket->id)
            ->where('event_type', SlaEventType::Pause)
            ->first();
        $this->assertNotNull($pauseEvent);
        $this->assertEquals('waiting_vendor', $pauseEvent->metadata['reason']);

        // Simulate 2 hours (120 minutes) pass in pending
        Carbon::setTestNow(now()->addMinutes(120));

        // 4. Resume to InProgress
        $workflowService->transition($ticket, TicketStatus::InProgress, $this->agent);

        $ticket->refresh();
        $this->assertFalse($ticket->is_sla_paused);
        $this->assertNull($ticket->last_paused_at);
        $this->assertGreaterThanOrEqual(7200, $ticket->total_paused_seconds);

        // Due date MUST be extended beyond initial due date!
        $this->assertTrue($ticket->resolution_due_at->gt($initialDue));

        $resumeEvent = TicketSlaEvent::where('ticket_id', $ticket->id)
            ->where('event_type', SlaEventType::Resume)
            ->first();
        $this->assertNotNull($resumeEvent);
        $this->assertGreaterThanOrEqual(7200, $resumeEvent->duration_seconds);

        Carbon::setTestNow();
    }

    public function test_sla_does_not_pause_on_unallowed_pending_reason(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 10, 16, 9, 0, 0, 'Asia/Makassar'));

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->createTicket($this->employee, [
            'subject' => 'Pertanyaan Konfigurasi',
            'description' => 'Butuh bantuan konfigurasi.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'impact' => Impact::Low->value,
            'urgency' => Urgency::Low->value, // P4
        ]);

        $ticketService->assignTicket($ticket, $this->agent, $this->supervisor);
        $workflowService = app(TicketWorkflowService::class);
        $workflowService->transition($ticket, TicketStatus::InProgress, $this->agent);

        // Put to Pending with 'other' (not allowed to pause)
        $workflowService->transition($ticket, TicketStatus::Pending, $this->agent, [
            'pending_reason' => PendingReason::Other->value,
            'pending_note' => 'Menunggu verifikasi internal.',
        ]);

        $ticket->refresh();
        $this->assertFalse($ticket->is_sla_paused);
        $this->assertNull($ticket->last_paused_at);

        Carbon::setTestNow();
    }

    public function test_first_response_recorded_on_agent_comment_but_not_requester(): void
    {
        $ticketService = app(TicketService::class);
        $ticket = $ticketService->createTicket($this->employee, [
            'subject' => 'Layar Rusak',
            'description' => 'Layar bergaris.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'impact' => Impact::Medium->value,
            'urgency' => Urgency::Medium->value,
        ]);

        $this->assertNull($ticket->first_responded_at);

        // Requester adds a comment -> first_responded_at should stay null
        $ticketService->addComment($ticket, $this->employee, 'Tambahan info: layar warna merah.', 'public');
        $this->assertNull($ticket->fresh()->first_responded_at);

        // Agent adds public comment -> first_responded_at is recorded
        $ticketService->addComment($ticket, $this->agent, 'Halo, teknisi segera memeriksa monitor.', 'public');
        $this->assertNotNull($ticket->fresh()->first_responded_at);
    }

    public function test_sla_checker_detects_warnings_and_breaches_idempotently(): void
    {
        Notification::fake();

        // Start at 08:00 WITA
        $start = Carbon::create(2026, 10, 16, 8, 0, 0, 'Asia/Makassar');
        Carbon::setTestNow($start);

        $ticketService = app(TicketService::class);
        $ticket = $ticketService->createTicket($this->employee, [
            'subject' => 'Server Down P1 Critical',
            'description' => 'Seluruh sistem mati mendadak.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'impact' => Impact::High->value,
            'urgency' => Urgency::High->value, // P1 (15 min response, 240 min resolution)
        ]);

        $ticketService->assignTicket($ticket, $this->agent, $this->supervisor);

        $slaService = app(SlaCalculatorService::class);

        // Advance 12 minutes (12 / 15 = 80% elapsed for first response >= 75% warning threshold)
        Carbon::setTestNow($start->copy()->addMinutes(12));

        $stats = $slaService->checkBreachesAndWarnings();
        $this->assertGreaterThanOrEqual(1, $stats['warnings_sent']);

        // Assert 75% warning notification sent
        Notification::assertSentTo($this->agent, SlaWarningNotification::class);

        // Run again at the exact same moment -> must be idempotent (0 new warnings)
        $statsSecondRun = $slaService->checkBreachesAndWarnings();
        $this->assertEquals(0, $statsSecondRun['warnings_sent']);

        // Advance past 15 minutes (e.g. 20 minutes) without first response -> breach!
        Carbon::setTestNow($start->copy()->addMinutes(20));

        $breachStats = $slaService->checkBreachesAndWarnings();
        $this->assertGreaterThanOrEqual(1, $breachStats['breaches_sent']);

        // Assert breach notification sent
        Notification::assertSentTo($this->agent, SlaBreachNotification::class);

        // Run again -> must be idempotent (0 new breaches)
        $breachStatsSecond = $slaService->checkBreachesAndWarnings();
        $this->assertEquals(0, $breachStatsSecond['breaches_sent']);

        Carbon::setTestNow();
    }

    public function test_user_can_view_notifications_and_mark_as_read(): void
    {
        $ticket = app(TicketService::class)->createTicket($this->employee, [
            'subject' => 'Tiket Uji Notifikasi',
            'description' => 'Deskripsi tiket uji.',
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'impact' => Impact::Medium->value,
            'urgency' => Urgency::High->value,
        ]);

        // Send a notification to the agent
        $this->agent->notify(new TicketAssignedNotification($ticket, $this->supervisor));

        $this->assertEquals(1, $this->agent->unreadNotifications()->count());

        // Agent views notifications index
        $response = $this->actingAs($this->agent)->get(route('notifications.index'));
        $response->assertStatus(200);
        $response->assertSee('Pusat Notifikasi');
        $response->assertSee($ticket->ticket_number);

        // Agent marks as read
        $notification = $this->agent->unreadNotifications()->first();
        $readResponse = $this->actingAs($this->agent)->post(route('notifications.markAsRead', $notification->id));
        $readResponse->assertRedirect(route('tickets.show', $ticket));

        $this->assertEquals(0, $this->agent->fresh()->unreadNotifications()->count());
    }
}
