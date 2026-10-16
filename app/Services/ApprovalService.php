<?php

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\HistoryEventType;
use App\Enums\PendingReason;
use App\Enums\TicketStatus;
use App\Models\Approval;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function __construct(
        private SlaCalculatorService $slaCalculator
    ) {}

    /**
     * Request an approval for a ticket.
     */
    public function requestApproval(Ticket $ticket, User $requester, ?User $approver = null): Approval
    {
        return DB::transaction(function () use ($ticket, $requester, $approver) {
            // Check for existing pending approval
            $existing = $ticket->approvals()->where('status', ApprovalStatus::Pending)->first();
            if ($existing) {
                return $existing;
            }

            // Find default approver if not provided (prioritizing 'approver' role)
            if (! $approver) {
                $approver = User::whereHas('roles', function ($q) {
                    $q->where('name', 'approver');
                })->where('is_active', true)->first()
                ?? User::whereHas('roles.permissions', function ($q) {
                    $q->where('name', 'approvals.decide');
                })->where('is_active', true)->first();
            }

            $approval = Approval::create([
                'ticket_id' => $ticket->id,
                'requested_by' => $requester->id,
                'approver_id' => $approver?->id,
                'status' => ApprovalStatus::Pending,
                'requested_at' => now(),
            ]);

            // Set ticket status to pending with reason waiting_approval
            $previousStatus = $ticket->status;
            $ticket->update([
                'status' => TicketStatus::Pending,
                'pending_reason' => PendingReason::WaitingApproval->value,
                'pending_note' => 'Menunggu persetujuan hak akses dari pejabat/approver yang berwenang.',
            ]);

            // Pause SLA due to pending approval
            $this->slaCalculator->handleStatusTransition(
                $ticket,
                $previousStatus,
                TicketStatus::Pending,
                PendingReason::WaitingApproval->value,
                'Menunggu persetujuan hak akses dari pejabat/approver yang berwenang.'
            );

            // Log history
            $ticket->histories()->create([
                'actor_id' => $requester->id,
                'event_type' => HistoryEventType::ApprovalRequested,
                'old_value' => ['status' => $previousStatus->value],
                'new_value' => [
                    'approval_id' => $approval->id,
                    'status' => ApprovalStatus::Pending->value,
                    'approver_name' => $approver?->name ?? 'Approver Ditunjuk',
                ],
                'reason' => 'Tiket memerlukan persetujuan sebelum dapat diproses.',
                'created_at' => now(),
            ]);

            return $approval;
        });
    }

    /**
     * Decide on an approval (Approve or Reject).
     *
     * @throws ValidationException
     */
    public function decide(
        Approval $approval,
        User $actor,
        ApprovalStatus $decision,
        ?string $reason = null
    ): Approval {
        if (! $approval->isPending()) {
            throw ValidationException::withMessages([
                'status' => ['Permintaan persetujuan ini sudah pernah diputuskan sebelumnya.'],
            ]);
        }

        // Validate permissions
        if (! $actor->hasPermission('approvals.decide') && $approval->approver_id !== $actor->id) {
            throw ValidationException::withMessages([
                'status' => ['Anda tidak memiliki wewenang untuk memutuskan persetujuan ini.'],
            ]);
        }

        // Rejection requires a mandatory reason
        if ($decision === ApprovalStatus::Rejected && empty(trim((string) $reason))) {
            throw ValidationException::withMessages([
                'reason' => ['Alasan penolakan wajib diisi agar pelapor memahami pertimbangannya.'],
            ]);
        }

        return DB::transaction(function () use ($approval, $actor, $decision, $reason) {
            $approval->update([
                'status' => $decision,
                'decision_reason' => $reason,
                'approver_id' => $approval->approver_id ?? $actor->id,
                'decided_at' => now(),
            ]);

            $ticket = $approval->ticket;

            // Record audit trail history
            $ticket->histories()->create([
                'actor_id' => $actor->id,
                'event_type' => HistoryEventType::ApprovalDecided,
                'old_value' => ['status' => ApprovalStatus::Pending->value],
                'new_value' => [
                    'approval_id' => $approval->id,
                    'status' => $decision->value,
                    'decision_reason' => $reason,
                    'decided_by' => $actor->name,
                ],
                'reason' => $reason,
                'created_at' => now(),
            ]);

            if ($decision === ApprovalStatus::Approved) {
                // Ticket is approved: transition to Open (or Assigned if already assigned), clearing pending state
                $targetStatus = $ticket->assignee_id ? TicketStatus::Assigned : TicketStatus::Open;
                $ticket->update([
                    'status' => $targetStatus,
                    'pending_reason' => null,
                    'pending_note' => null,
                ]);

                // Resume SLA
                $this->slaCalculator->handleStatusTransition(
                    $ticket,
                    TicketStatus::Pending,
                    $targetStatus
                );

                $ticket->histories()->create([
                    'actor_id' => $actor->id,
                    'event_type' => HistoryEventType::StatusChanged,
                    'old_value' => ['status' => TicketStatus::Pending->value],
                    'new_value' => ['status' => $targetStatus->value],
                    'reason' => 'Persetujuan disetujui. Tiket siap diproses oleh teknisi/tim IT.',
                    'created_at' => now(),
                ]);
            } else {
                // Ticket is rejected: cancel ticket
                $ticket->update([
                    'status' => TicketStatus::Cancelled,
                    'pending_reason' => null,
                    'pending_note' => null,
                ]);

                // Resume/Cancel SLA
                $this->slaCalculator->handleStatusTransition(
                    $ticket,
                    TicketStatus::Pending,
                    TicketStatus::Cancelled
                );

                $ticket->histories()->create([
                    'actor_id' => $actor->id,
                    'event_type' => HistoryEventType::Cancelled,
                    'old_value' => ['status' => TicketStatus::Pending->value],
                    'new_value' => ['status' => TicketStatus::Cancelled->value],
                    'reason' => 'Permintaan dibatalkan karena persetujuan ditolak: '.$reason,
                    'created_at' => now(),
                ]);
            }

            return $approval->fresh();
        });
    }

    /**
     * Get pending approvals for user.
     */
    public function getPendingApprovals(User $user)
    {
        $query = Approval::with(['ticket.category', 'ticket.requester.department', 'requester', 'approver'])
            ->where('status', ApprovalStatus::Pending);

        if (! $user->hasPermission('tickets.view_all')) {
            $query->where(function ($q) use ($user) {
                $q->where('approver_id', $user->id)
                    ->orWhereNull('approver_id');
            });
        }

        return $query->orderBy('requested_at', 'desc')->paginate(15);
    }
}
