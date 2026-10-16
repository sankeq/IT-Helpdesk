<?php

namespace Tests\Feature;

use App\Enums\ApprovalStatus;
use App\Enums\HistoryEventType;
use App\Enums\PendingReason;
use App\Enums\TicketStatus;
use App\Models\Approval;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for Scenario B: Permintaan Akses & Approval Workflow (TEST_PLAN.md).
 */
class TicketApprovalTest extends TestCase
{
    use RefreshDatabase;

    private User $employee;

    private User $approver;

    private User $supervisor;

    private User $agent;

    private TicketCategory $accessCategory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->employee = User::where('email', 'employee@iki.co.id')->first();
        $this->approver = User::where('email', 'approver@iki.co.id')->first();
        $this->supervisor = User::where('email', 'supervisor@iki.co.id')->first();
        $this->agent = User::where('email', 'agent@iki.co.id')->first();
        $this->accessCategory = TicketCategory::where('code', 'REQ-ACC')->first();
    }

    public function test_creating_ticket_in_access_category_triggers_approval_and_sets_waiting_approval(): void
    {
        $this->assertTrue($this->accessCategory->requiresApproval());

        $payload = [
            'category_id' => $this->accessCategory->id,
            'subject' => 'Permintaan Akses Modul Keuangan Galangan ERP',
            'description' => 'Memohon aktivasi hak akses modul general ledger untuk staf akuntansi baru.',
            'impact' => 'medium',
            'urgency' => 'medium',
        ];

        $response = $this->actingAs($this->employee)
            ->post(route('tickets.store'), $payload);

        $response->assertRedirect();

        $ticket = Ticket::where('subject', $payload['subject'])->first();
        $this->assertNotNull($ticket);
        $this->assertEquals(TicketStatus::Pending, $ticket->status);
        $this->assertEquals(PendingReason::WaitingApproval, $ticket->pending_reason);

        // Verify Approval record created
        $approval = $ticket->latestApproval;
        $this->assertNotNull($approval);
        $this->assertEquals(ApprovalStatus::Pending, $approval->status);
        $this->assertEquals($this->employee->id, $approval->requested_by);
        $this->assertEquals($this->approver->id, $approval->approver_id);

        // Verify History event logged
        $hasApprovalHistory = $ticket->histories()
            ->where('event_type', HistoryEventType::ApprovalRequested)
            ->exists();
        $this->assertTrue($hasApprovalHistory);
    }

    public function test_unauthorized_user_cannot_access_or_decide_approval(): void
    {
        // Create access ticket
        $ticket = $this->createAccessTicket();
        $approval = $ticket->latestApproval;

        // Another employee tries to view approvals
        $otherEmployee = User::where('email', 'karyawan@iki.co.id')->first();
        $indexResponse = $this->actingAs($otherEmployee)->get(route('approvals.index'));
        $indexResponse->assertForbidden();

        // Other employee tries to decide
        $decideResponse = $this->actingAs($otherEmployee)
            ->post(route('approvals.decide', $approval), [
                'decision' => 'approved',
            ]);
        $decideResponse->assertForbidden();

        // Approval status remains pending
        $this->assertEquals(ApprovalStatus::Pending, $approval->fresh()->status);
    }

    public function test_approver_can_approve_request_and_agent_can_subsequently_process_ticket(): void
    {
        // 1. Employee creates access ticket
        $ticket = $this->createAccessTicket();
        $approval = $ticket->latestApproval;
        $this->assertEquals(TicketStatus::Pending, $ticket->status);

        // 2. Approver views approvals dashboard
        $viewResponse = $this->actingAs($this->approver)->get(route('approvals.index'));
        $viewResponse->assertOk();
        $viewResponse->assertSee($ticket->ticket_number);

        // 3. Approver approves the request
        $decideResponse = $this->actingAs($this->approver)
            ->post(route('approvals.decide', $approval), [
                'decision' => 'approved',
                'reason' => 'Disetujui sesuai kebutuhan operasional divisi keuangan.',
            ]);

        $decideResponse->assertRedirect();
        $decideResponse->assertSessionHas('success');

        $this->assertEquals(ApprovalStatus::Approved, $approval->fresh()->status);
        $this->assertEquals('Disetujui sesuai kebutuhan operasional divisi keuangan.', $approval->fresh()->decision_reason);

        // 4. Ticket is unblocked from pending (becomes Open ready for assignment)
        $ticket->refresh();
        $this->assertEquals(TicketStatus::Open, $ticket->status);
        $this->assertNull($ticket->pending_reason);

        // 5. Supervisor assigns technician
        $assignResponse = $this->actingAs($this->supervisor)
            ->post(route('tickets.assign', $ticket), [
                'assignee_id' => $this->agent->id,
            ]);
        $assignResponse->assertRedirect();
        $ticket->refresh();
        $this->assertEquals(TicketStatus::Assigned, $ticket->status);
        $this->assertEquals($this->agent->id, $ticket->assignee_id);

        // 6. Agent begins work (in_progress)
        $progressResponse = $this->actingAs($this->agent)
            ->post(route('tickets.transition', $ticket), [
                'status' => 'in_progress',
            ]);
        $progressResponse->assertRedirect();
        $this->assertEquals(TicketStatus::InProgress, $ticket->fresh()->status);

        // 7. Agent resolves ticket
        $resolveResponse = $this->actingAs($this->agent)
            ->post(route('tickets.transition', $ticket), [
                'status' => 'resolved',
                'resolution_summary' => 'Akun ERP telah diaktifkan dengan role GL Staff.',
            ]);
        $resolveResponse->assertRedirect();
        $this->assertEquals(TicketStatus::Resolved, $ticket->fresh()->status);
    }

    public function test_approver_rejection_requires_reason_and_cancels_ticket(): void
    {
        $ticket = $this->createAccessTicket();
        $approval = $ticket->latestApproval;

        // Rejection without reason fails
        $failResponse = $this->actingAs($this->approver)
            ->post(route('approvals.decide', $approval), [
                'decision' => 'rejected',
                'reason' => '',
            ]);
        $failResponse->assertSessionHasErrors(['reason']);
        $this->assertEquals(ApprovalStatus::Pending, $approval->fresh()->status);

        // Rejection with reason succeeds
        $successResponse = $this->actingAs($this->approver)
            ->post(route('approvals.decide', $approval), [
                'decision' => 'rejected',
                'reason' => 'Pemohon belum menyelesaikan pelatihan wajib kepatuhan data.',
            ]);

        $successResponse->assertRedirect();
        $this->assertEquals(ApprovalStatus::Rejected, $approval->fresh()->status);

        // Ticket is cancelled
        $ticket->refresh();
        $this->assertEquals(TicketStatus::Cancelled, $ticket->status);

        // History reflects cancellation with reason
        $hasCancelHistory = $ticket->histories()
            ->where('event_type', HistoryEventType::Cancelled)
            ->where('reason', 'like', '%pelatihan wajib%')
            ->exists();
        $this->assertTrue($hasCancelHistory);
    }

    private function createAccessTicket(): Ticket
    {
        $payload = [
            'category_id' => $this->accessCategory->id,
            'subject' => 'Permintaan Akses Database Galangan',
            'description' => 'Memohon akses read-only ke database galangan kapal.',
            'impact' => 'low',
            'urgency' => 'low',
        ];

        $this->actingAs($this->employee)->post(route('tickets.store'), $payload);

        return Ticket::where('subject', $payload['subject'])->firstOrFail();
    }
}
