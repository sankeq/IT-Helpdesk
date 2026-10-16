<?php

namespace Tests\Unit;

use App\Enums\CommentVisibility;
use App\Enums\HistoryEventType;
use App\Enums\Impact;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Location;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketService $ticketService;

    private User $employee;

    private User $agent;

    private TicketCategory $category;

    private Location $location;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->ticketService = app(TicketService::class);
        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();
        $this->location = Location::first();
    }

    public function test_priority_matrix_matches_specification(): void
    {
        // High Impact
        $this->assertEquals(Priority::P1, Priority::fromMatrix(Impact::High, Urgency::High));
        $this->assertEquals(Priority::P2, Priority::fromMatrix(Impact::High, Urgency::Medium));
        $this->assertEquals(Priority::P2, Priority::fromMatrix(Impact::High, Urgency::Low));

        // Medium Impact
        $this->assertEquals(Priority::P2, Priority::fromMatrix(Impact::Medium, Urgency::High));
        $this->assertEquals(Priority::P3, Priority::fromMatrix(Impact::Medium, Urgency::Medium));
        $this->assertEquals(Priority::P3, Priority::fromMatrix(Impact::Medium, Urgency::Low));

        // Low Impact
        $this->assertEquals(Priority::P3, Priority::fromMatrix(Impact::Low, Urgency::High));
        $this->assertEquals(Priority::P4, Priority::fromMatrix(Impact::Low, Urgency::Medium));
        $this->assertEquals(Priority::P4, Priority::fromMatrix(Impact::Low, Urgency::Low));
    }

    public function test_it_creates_ticket_with_number_and_audit_history(): void
    {
        $ticket = $this->ticketService->createTicket(
            $this->employee,
            [
                'subject' => 'Printer Galangan Macet',
                'description' => 'Kertas tersangkut pada printer Epson L3110 di Pos 2.',
                'category_id' => $this->category->id,
                'location_id' => $this->location->id,
                'impact' => Impact::Low->value,
                'urgency' => Urgency::Medium->value,
            ]
        );

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertStringStartsWith('IKI-IT-', $ticket->ticket_number);
        $this->assertEquals(TicketStatus::Open, $ticket->status);
        $this->assertEquals(Priority::P4, $ticket->priority);
        $this->assertEquals($this->employee->id, $ticket->requester_id);

        // Verify audit history created
        $history = $ticket->histories()->first();
        $this->assertNotNull($history);
        $this->assertEquals(HistoryEventType::TicketCreated, $history->event_type);
        $this->assertEquals($this->employee->id, $history->actor_id);
    }

    public function test_it_assigns_ticket_and_transitions_status(): void
    {
        $ticket = $this->ticketService->createTicket(
            $this->employee,
            [
                'subject' => 'Kendala Jaringan LAN',
                'description' => 'Switch lampu merah berkedip.',
                'category_id' => $this->category->id,
                'impact' => Impact::Medium->value,
                'urgency' => Urgency::High->value,
            ]
        );

        $supervisor = User::where('email', 'supervisor@iki.co.id')->first();

        $updatedTicket = $this->ticketService->assignTicket(
            $ticket,
            $this->agent,
            $supervisor,
            'Mohon segera dicek langsung ke lokasi.'
        );

        $this->assertEquals($this->agent->id, $updatedTicket->assignee_id);
        $this->assertEquals(TicketStatus::Assigned, $updatedTicket->status);

        // Verify audit history for assignment
        $assignHistory = $updatedTicket->histories()
            ->where('event_type', HistoryEventType::Assigned)
            ->first();

        $this->assertNotNull($assignHistory);
        $this->assertEquals($supervisor->id, $assignHistory->actor_id);
        $this->assertEquals($this->agent->name, $assignHistory->new_value['assignee_name']);
    }

    public function test_it_adds_public_and_internal_comments(): void
    {
        $ticket = $this->ticketService->createTicket(
            $this->employee,
            [
                'subject' => 'Aplikasi ERP Error',
                'description' => 'Error 500 saat simpan data PO.',
                'category_id' => $this->category->id,
                'impact' => Impact::Medium->value,
                'urgency' => Urgency::Medium->value,
            ]
        );

        // Public comment by requester
        $comment1 = $this->ticketService->addComment(
            $ticket,
            $this->employee,
            'Lampiran tambahan sudah saya siapkan.',
            CommentVisibility::Public->value
        );

        // Internal comment by IT agent
        $comment2 = $this->ticketService->addComment(
            $ticket,
            $this->agent,
            'Database deadlock terdeteksi di log MariaDB.',
            CommentVisibility::Internal->value
        );

        $this->assertEquals(CommentVisibility::Public, $comment1->visibility);
        $this->assertEquals(CommentVisibility::Internal, $comment2->visibility);
        $this->assertEquals(2, $ticket->comments()->count());
    }

    public function test_it_stores_attachment_securely(): void
    {
        Storage::fake('local');

        $ticket = $this->ticketService->createTicket(
            $this->employee,
            [
                'subject' => 'Screenshot Error',
                'description' => 'Ada screenshot terlampir.',
                'category_id' => $this->category->id,
                'impact' => Impact::Low->value,
                'urgency' => Urgency::Low->value,
            ]
        );

        $file = UploadedFile::fake()->create('error_log.txt', 25, 'text/plain');

        $attachment = $this->ticketService->storeAttachment(
            $ticket,
            $this->employee,
            $file
        );

        $this->assertEquals('error_log.txt', $attachment->original_filename);
        $this->assertEquals('text/plain', $attachment->mime_type);
        Storage::disk('local')->assertExists($attachment->storage_key);

        // Check history event
        $history = $ticket->histories()->where('event_type', HistoryEventType::AttachmentAdded)->first();
        $this->assertNotNull($history);
    }
}
