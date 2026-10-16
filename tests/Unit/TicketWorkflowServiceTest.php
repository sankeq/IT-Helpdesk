<?php

namespace Tests\Unit;

use App\Enums\HistoryEventType;
use App\Enums\Impact;
use App\Enums\PendingReason;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Services\TicketService;
use App\Services\TicketWorkflowService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TicketWorkflowServiceTest extends TestCase
{
    use RefreshDatabase;

    private TicketWorkflowService $workflowService;

    private TicketService $ticketService;

    private User $employee;

    private User $agent;

    private User $supervisor;

    private User $admin;

    private TicketCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->workflowService = app(TicketWorkflowService::class);
        $this->ticketService = app(TicketService::class);

        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->admin = User::where('email', 'admin@iki.co.id')->first();
        $this->category = TicketCategory::whereNotNull('parent_id')->first();
    }

    private function createTestTicket(): Ticket
    {
        return $this->ticketService->createTicket(
            $this->employee,
            [
                'subject' => 'Kendala Koneksi Wi-Fi',
                'description' => 'Tidak bisa terhubung ke SSID IKI-Office.',
                'category_id' => $this->category->id,
                'impact' => Impact::Low->value,
                'urgency' => Urgency::Low->value,
            ]
        );
    }

    public function test_full_successful_lifecycle_transition(): void
    {
        $ticket = $this->createTestTicket();
        $this->assertEquals(TicketStatus::Open, $ticket->status);

        // 1. Open -> Assigned
        $ticket = $this->workflowService->transition($ticket, TicketStatus::Assigned, $this->supervisor, [
            'assignee_id' => $this->agent->id,
        ]);
        $this->assertEquals(TicketStatus::Assigned, $ticket->status);
        $this->assertEquals($this->agent->id, $ticket->assignee_id);

        // 2. Assigned -> In Progress
        $ticket = $this->workflowService->transition($ticket, TicketStatus::InProgress, $this->agent);
        $this->assertEquals(TicketStatus::InProgress, $ticket->status);

        // 3. In Progress -> Pending
        $ticket = $this->workflowService->transition($ticket, TicketStatus::Pending, $this->agent, [
            'pending_reason' => PendingReason::WaitingParts->value,
            'pending_note' => 'Menunggu router pengganti dari gudang IT.',
        ]);
        $this->assertEquals(TicketStatus::Pending, $ticket->status);
        $this->assertEquals(PendingReason::WaitingParts, $ticket->pending_reason);
        $this->assertEquals('Menunggu router pengganti dari gudang IT.', $ticket->pending_note);

        // 4. Pending -> In Progress
        $ticket = $this->workflowService->transition($ticket, TicketStatus::InProgress, $this->agent);
        $this->assertEquals(TicketStatus::InProgress, $ticket->status);
        $this->assertNull($ticket->pending_reason);
        $this->assertNull($ticket->pending_note);

        // 5. In Progress -> Resolved
        $ticket = $this->workflowService->transition($ticket, TicketStatus::Resolved, $this->agent, [
            'resolution_summary' => 'Router AP diganti dengan unit baru dan SSID kembali online.',
        ]);
        $this->assertEquals(TicketStatus::Resolved, $ticket->status);
        $this->assertNotNull($ticket->resolved_at);
        $this->assertEquals('Router AP diganti dengan unit baru dan SSID kembali online.', $ticket->resolution_summary);

        // 6. Resolved -> Closed
        $ticket = $this->workflowService->transition($ticket, TicketStatus::Closed, $this->employee, [
            'note' => 'Konfirmasi koneksi sudah lancar. Terima kasih.',
        ]);
        $this->assertEquals(TicketStatus::Closed, $ticket->status);
        $this->assertNotNull($ticket->closed_at);
    }

    public function test_invalid_status_transition_is_rejected(): void
    {
        $ticket = $this->createTestTicket();

        // Direct Open -> Resolved must fail
        $this->expectException(ValidationException::class);
        $this->workflowService->transition($ticket, TicketStatus::Resolved, $this->agent, [
            'resolution_summary' => 'Langsung selesai tanpa assign.',
        ]);
    }

    public function test_closed_ticket_cannot_transition(): void
    {
        $ticket = $this->createTestTicket();
        $ticket->update(['status' => TicketStatus::Closed]);

        $this->expectException(ValidationException::class);
        $this->workflowService->transition($ticket, TicketStatus::InProgress, $this->admin);
    }

    public function test_pending_requires_reason(): void
    {
        $ticket = $this->createTestTicket();
        $ticket->update(['status' => TicketStatus::InProgress, 'assignee_id' => $this->agent->id]);

        $this->expectException(ValidationException::class);
        // Missing pending_reason
        $this->workflowService->transition($ticket, TicketStatus::Pending, $this->agent, []);
    }

    public function test_resolve_requires_resolution_summary(): void
    {
        $ticket = $this->createTestTicket();
        $ticket->update(['status' => TicketStatus::InProgress, 'assignee_id' => $this->agent->id]);

        $this->expectException(ValidationException::class);
        // Missing resolution_summary
        $this->workflowService->transition($ticket, TicketStatus::Resolved, $this->agent, []);
    }

    public function test_unauthorized_user_cannot_transition(): void
    {
        $ticket = $this->createTestTicket();

        // Employee does not have tickets.assign permission
        $this->expectException(ValidationException::class);
        $this->workflowService->transition($ticket, TicketStatus::Assigned, $this->employee, [
            'assignee_id' => $this->agent->id,
        ]);
    }

    public function test_reopen_resolved_ticket(): void
    {
        $ticket = $this->createTestTicket();
        $ticket->update([
            'status' => TicketStatus::Resolved,
            'assignee_id' => $this->agent->id,
            'resolution_summary' => 'Selesai sementara.',
            'resolved_at' => now(),
        ]);

        // Employee can reopen resolved ticket to Reopened status
        $updatedTicket = $this->workflowService->transition($ticket, TicketStatus::Reopened, $this->employee, [
            'reason' => 'Masalah muncul kembali saat laptop restart.',
        ]);

        $this->assertEquals(TicketStatus::Reopened, $updatedTicket->status);
        $this->assertNull($updatedTicket->resolved_at);

        // Check history event
        $reopenHistory = $updatedTicket->histories()
            ->where('event_type', HistoryEventType::Reopened)
            ->first();

        $this->assertNotNull($reopenHistory);
    }
}
