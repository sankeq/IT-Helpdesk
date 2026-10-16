<?php

namespace Tests\Feature;

use App\Enums\Impact;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Department;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $employee2;

    private User $agent;

    private User $supervisor;

    private User $admin;

    private TicketCategory $category;

    private Location $location;

    private Department $department;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->employee2 = User::where('email', 'karyawan@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->admin = User::where('email', 'admin@iki.co.id')->first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();
        $this->location = Location::first();
        $this->department = Department::first();
    }

    /**
     * Helper to create a ticket with custom attributes.
     */
    private function createTicket(array $overrides = []): Ticket
    {
        return Ticket::create(array_merge([
            'ticket_number' => 'TICK-TEST-'.uniqid(),
            'requester_id' => $this->employee->id,
            'category_id' => $this->category->id,
            'location_id' => $this->location->id,
            'subject' => 'Test Ticket Subject',
            'description' => 'Test Ticket Description for Dashboard/Report tests',
            'status' => TicketStatus::Open->value,
            'priority' => Priority::P3->value,
            'urgency' => Urgency::Medium->value,
            'impact' => Impact::Medium->value,
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────
    // 1. Role-Tailored Dashboards (US-025, US-026, US-027)
    // ─────────────────────────────────────────────────────────────

    public function test_employee_dashboard_renders_personal_overview(): void
    {
        $this->createTicket([
            'requester_id' => $this->employee->id,
            'subject' => 'Laptop Lambat Karyawan',
        ]);

        $response = $this->actingAs($this->employee)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSeeText('Selamat Datang, '.$this->employee->name);
        $response->assertSee('Laptop Lambat Karyawan');
        $response->assertSeeText('Tiket Terkini');
    }

    public function test_agent_dashboard_renders_unassigned_queue_and_urgent_tickets(): void
    {
        // Unassigned ticket
        $unassigned = $this->createTicket([
            'subject' => 'Server Galangan Down Unassigned',
            'assignee_id' => null,
            'status' => TicketStatus::Open->value,
        ]);

        // Ticket assigned to agent
        $assigned = $this->createTicket([
            'subject' => 'Router Docking Rusak',
            'assignee_id' => $this->agent->id,
            'status' => TicketStatus::InProgress->value,
        ]);

        $response = $this->actingAs($this->agent)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSeeText('Antrean Tiket Terbuka (Belum Ditugaskan)');
        $response->assertSee('Server Galangan Down Unassigned');
        $response->assertSee('Router Docking Rusak');
    }

    public function test_supervisor_dashboard_renders_management_kpi_and_workloads(): void
    {
        $this->createTicket([
            'assignee_id' => $this->agent->id,
            'status' => TicketStatus::InProgress->value,
        ]);

        $response = $this->actingAs($this->supervisor)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSeeText('Ringkasan Operasional & Kepatuhan SLA');
        $response->assertSeeText('Buka Laporan & Analitik Lengkap');
        $response->assertSeeText('Kepatuhan SLA Resolusi');
    }

    // ─────────────────────────────────────────────────────────────
    // 2. Reporting & Analytics Authorization (US-028)
    // ─────────────────────────────────────────────────────────────

    public function test_reports_page_forbidden_for_regular_employee(): void
    {
        $response = $this->actingAs($this->employee)->get(route('reports.index'));
        $response->assertStatus(403);
    }

    public function test_reports_page_forbidden_for_it_agent_without_permission(): void
    {
        $response = $this->actingAs($this->agent)->get(route('reports.index'));
        $response->assertStatus(403);
    }

    public function test_reports_page_accessible_by_supervisor(): void
    {
        $response = $this->actingAs($this->supervisor)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertSeeText('Laporan Kinerja Layanan Helpdesk');
        $response->assertSeeText('Volume Tiket');
        $response->assertSeeText('Tingkat Penyelesaian');
        $response->assertSeeText('Kepatuhan SLA Resolusi');
    }

    public function test_reports_page_accessible_by_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('reports.index'));
        $response->assertStatus(200);
        $response->assertSeeText('Laporan Kinerja Layanan Helpdesk');
    }

    // ─────────────────────────────────────────────────────────────
    // 3. Multi-Dimensional Filtering & KPI Metrics
    // ─────────────────────────────────────────────────────────────

    public function test_reports_filters_by_status_priority_and_category(): void
    {
        $catA = $this->category;
        $catB = TicketCategory::where('id', '!=', $catA->id)->whereNotNull('parent_id')->first();

        // Ticket matching filter
        $this->createTicket([
            'subject' => 'Tiket P1 Hardware Kritis',
            'priority' => Priority::P1->value,
            'status' => TicketStatus::Resolved->value,
            'category_id' => $catA->id,
            'resolved_at' => Carbon::now('UTC'),
        ]);

        // Ticket not matching filter
        $this->createTicket([
            'subject' => 'Tiket P4 Software Rendah',
            'priority' => Priority::P4->value,
            'status' => TicketStatus::Open->value,
            'category_id' => $catB->id,
        ]);

        $response = $this->actingAs($this->supervisor)->get(route('reports.index', [
            'priority' => Priority::P1->value,
            'status' => TicketStatus::Resolved->value,
            'category_id' => $catA->id,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Tiket P1 Hardware Kritis');
        $response->assertDontSee('Tiket P4 Software Rendah');
    }

    // ─────────────────────────────────────────────────────────────
    // 4. Secure CSV Export & Formula Injection (CWE-1236) (US-029)
    // ─────────────────────────────────────────────────────────────

    public function test_csv_export_forbidden_for_regular_employee(): void
    {
        $response = $this->actingAs($this->employee)->get(route('reports.export'));
        $response->assertStatus(403);
    }

    public function test_csv_export_generates_valid_stream_with_utf8_bom(): void
    {
        $this->createTicket(['subject' => 'Tiket Export Normal']);

        $response = $this->actingAs($this->supervisor)->get(route('reports.export'));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=laporan-tiket-iki-', $response->headers->get('Content-Disposition'));

        // Capture streamed response content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Check for UTF-8 BOM: \xEF\xBB\xBF
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('No. Tiket', $content);
        $this->assertStringContainsString('Tiket Export Normal', $content);
    }

    public function test_csv_export_mitigates_formula_injection_cwe_1236(): void
    {
        // Malicious subjects that would trigger spreadsheet formula execution
        $maliciousTickets = [
            '=cmd|\'/C calc\'!A0',
            '+SUM(A1:A10)',
            '-2+5+cmd',
            '@SUM(1+1)',
            "\tTAB_INJECT",
            "\rCR_INJECT",
        ];

        foreach ($maliciousTickets as $idx => $maliciousSubject) {
            $this->createTicket([
                'ticket_number' => 'TICK-INJ-'.$idx,
                'subject' => $maliciousSubject,
            ]);
        }

        $response = $this->actingAs($this->supervisor)->get(route('reports.export'));
        $response->assertStatus(200);

        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Assert dangerous formula symbols are escaped with a leading single quote
        $this->assertStringContainsString("'=cmd|", $content);
        $this->assertStringContainsString("'+SUM", $content);
        $this->assertStringContainsString("'-2+5", $content);
        $this->assertStringContainsString("'@SUM", $content);
    }

    // ─────────────────────────────────────────────────────────────
    // 5. CSAT Customer Satisfaction Rating
    // ─────────────────────────────────────────────────────────────

    public function test_requester_can_submit_csat_rating_on_resolved_ticket(): void
    {
        $ticket = $this->createTicket([
            'requester_id' => $this->employee->id,
            'status' => TicketStatus::Resolved->value,
            'resolved_at' => Carbon::now('UTC'),
        ]);

        $response = $this->actingAs($this->employee)->post(route('tickets.rate', $ticket), [
            'rating' => 5,
            'rating_comment' => 'Penanganan sangat cepat dan ramah, terima kasih tim IT IKI!',
        ]);

        $response->assertRedirect(route('tickets.show', $ticket));
        $response->assertSessionHas('success', 'Terima kasih atas penilaian dan masukan Anda untuk peningkatan layanan IT IKI.');

        $ticket->refresh();
        $this->assertEquals(5, $ticket->rating);
        $this->assertEquals('Penanganan sangat cepat dan ramah, terima kasih tim IT IKI!', $ticket->rating_comment);
        $this->assertNotNull($ticket->rated_at);
        $this->assertTrue($ticket->isRated());
    }

    public function test_cannot_rate_ticket_that_is_still_open(): void
    {
        $ticket = $this->createTicket([
            'requester_id' => $this->employee->id,
            'status' => TicketStatus::Open->value,
        ]);

        $response = $this->actingAs($this->employee)->post(route('tickets.rate', $ticket), [
            'rating' => 5,
        ]);

        $response->assertStatus(403);
    }

    public function test_non_requester_cannot_rate_ticket(): void
    {
        $ticket = $this->createTicket([
            'requester_id' => $this->employee->id,
            'status' => TicketStatus::Resolved->value,
            'resolved_at' => Carbon::now('UTC'),
        ]);

        // Attempting to rate by another employee
        $response = $this->actingAs($this->employee2)->post(route('tickets.rate', $ticket), [
            'rating' => 5,
        ]);

        $response->assertStatus(403);
    }

    public function test_csat_rating_validation_rejects_out_of_bounds_values(): void
    {
        $ticket = $this->createTicket([
            'requester_id' => $this->employee->id,
            'status' => TicketStatus::Resolved->value,
            'resolved_at' => Carbon::now('UTC'),
        ]);

        // Rating = 6 (over max 5)
        $response = $this->actingAs($this->employee)->post(route('tickets.rate', $ticket), [
            'rating' => 6,
        ]);
        $response->assertSessionHasErrors('rating');

        // Rating = 0 (under min 1)
        $response = $this->actingAs($this->employee)->post(route('tickets.rate', $ticket), [
            'rating' => 0,
        ]);
        $response->assertSessionHasErrors('rating');
    }

    public function test_csat_average_is_correctly_displayed_in_reports(): void
    {
        // Ticket 1: 4 stars
        $this->createTicket([
            'status' => TicketStatus::Resolved->value,
            'rating' => 4,
            'rated_at' => Carbon::now('UTC'),
        ]);

        // Ticket 2: 5 stars
        $this->createTicket([
            'status' => TicketStatus::Closed->value,
            'rating' => 5,
            'rated_at' => Carbon::now('UTC'),
        ]);

        $response = $this->actingAs($this->supervisor)->get(route('reports.index'));

        $response->assertStatus(200);
        // Average is (4+5)/2 = 4.5
        $response->assertSee('4.5');
    }
}
