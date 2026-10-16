<?php

namespace App\Services;

use App\Enums\HistoryEventType;
use App\Enums\PendingReason;
use App\Enums\TicketStatus;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Manages ticket status transitions with validation and audit logging.
 * Implements the state machine defined in WORKFLOW_SPEC.md.
 */
class TicketWorkflowService
{
    public function __construct(
        private SlaCalculatorService $slaCalculator
    ) {}

    /**
     * Transition a ticket to a new status with validation.
     *
     * @throws ValidationException
     */
    public function transition(
        Ticket $ticket,
        TicketStatus $newStatus,
        User $actor,
        array $data = []
    ): Ticket {
        $currentStatus = $ticket->status;

        // Validate the transition is allowed
        if (! $currentStatus->canTransitionTo($newStatus)) {
            throw ValidationException::withMessages([
                'status' => ["Transisi dari {$currentStatus->label()} ke {$newStatus->label()} tidak diizinkan."],
            ]);
        }

        // Validate actor has required permissions
        $this->validateActorPermissions($ticket, $currentStatus, $newStatus, $actor);

        // Validate required data for specific transitions
        $this->validateTransitionData($newStatus, $data);

        return DB::transaction(function () use ($ticket, $currentStatus, $newStatus, $actor, $data) {
            $updates = ['status' => $newStatus];
            $eventType = HistoryEventType::StatusChanged;

            switch ($newStatus) {
                case TicketStatus::Assigned:
                    if (isset($data['assignee_id'])) {
                        $updates['assignee_id'] = $data['assignee_id'];
                    }
                    break;

                case TicketStatus::Pending:
                    $updates['pending_reason'] = $data['pending_reason'];
                    $updates['pending_note'] = $data['pending_note'] ?? null;
                    break;

                case TicketStatus::Resolved:
                    $updates['resolution_summary'] = $data['resolution_summary'];
                    $updates['resolved_at'] = now();
                    $updates['pending_reason'] = null;
                    $updates['pending_note'] = null;
                    $eventType = HistoryEventType::Resolved;
                    break;

                case TicketStatus::Closed:
                    $updates['closed_at'] = now();
                    $eventType = HistoryEventType::Closed;
                    break;

                case TicketStatus::Reopened:
                    $updates['resolution_summary'] = null;
                    $updates['resolved_at'] = null;
                    $updates['closed_at'] = null;
                    $updates['pending_reason'] = null;
                    $updates['pending_note'] = null;
                    $eventType = HistoryEventType::Reopened;
                    break;

                case TicketStatus::Cancelled:
                    $eventType = HistoryEventType::Cancelled;
                    break;

                case TicketStatus::InProgress:
                    $updates['pending_reason'] = null;
                    $updates['pending_note'] = null;
                    break;

                default:
                    break;
            }

            $ticket->update($updates);

            // Record history
            $ticket->histories()->create([
                'actor_id' => $actor->id,
                'event_type' => $eventType,
                'old_value' => ['status' => $currentStatus->value],
                'new_value' => array_merge(
                    ['status' => $newStatus->value],
                    isset($data['resolution_summary']) ? ['resolution_summary' => $data['resolution_summary']] : [],
                    isset($data['pending_reason']) ? ['pending_reason' => $data['pending_reason']] : [],
                ),
                'reason' => $data['reason'] ?? $data['pending_note'] ?? null,
                'created_at' => now(),
            ]);

            // Handle SLA pause/resume transitions
            $this->slaCalculator->handleStatusTransition(
                $ticket,
                $currentStatus,
                $newStatus,
                $data['pending_reason'] ?? null,
                $data['pending_note'] ?? null
            );

            // Starting progress on a ticket counts as first response
            if ($newStatus === TicketStatus::InProgress) {
                $this->slaCalculator->recordFirstResponse($ticket, $actor);
            }

            return $ticket->fresh();
        });
    }

    /**
     * Validate actor has permission for the specific transition.
     */
    private function validateActorPermissions(
        Ticket $ticket,
        TicketStatus $from,
        TicketStatus $to,
        User $actor
    ): void {
        $requiredPermissions = $from->allowedTransitions()[$to->value] ?? [];

        // Check if actor has at least one of the required permissions
        $hasPermission = false;
        foreach ($requiredPermissions as $permission) {
            if ($actor->hasPermission($permission)) {
                $hasPermission = true;
                break;
            }
        }

        if (! $hasPermission) {
            throw ValidationException::withMessages([
                'status' => ['Anda tidak memiliki izin untuk melakukan perubahan status ini.'],
            ]);
        }

        // Additional actor-specific validations based on WORKFLOW_SPEC.md
        switch ($to) {
            case TicketStatus::InProgress:
                // Only assignee can start work
                if ($from === TicketStatus::Assigned || $from === TicketStatus::Reopened) {
                    if (! $ticket->isAssignedTo($actor) && ! $actor->hasPermission('tickets.assign')) {
                        throw ValidationException::withMessages([
                            'status' => ['Hanya teknisi yang ditugaskan yang dapat memulai penanganan.'],
                        ]);
                    }
                }
                break;

            case TicketStatus::Resolved:
                // Only assignee can resolve
                if (! $ticket->isAssignedTo($actor) && ! $actor->hasPermission('tickets.assign')) {
                    throw ValidationException::withMessages([
                        'status' => ['Hanya teknisi yang ditugaskan yang dapat menyelesaikan tiket.'],
                    ]);
                }
                break;

            case TicketStatus::Closed:
                // Requester, authorized agent, or supervisor
                if (! $ticket->isOwnedBy($actor) && ! $actor->hasPermission('tickets.close')) {
                    throw ValidationException::withMessages([
                        'status' => ['Anda tidak memiliki izin untuk menutup tiket ini.'],
                    ]);
                }
                break;

            case TicketStatus::Reopened:
                if ($from === TicketStatus::Closed) {
                    // Only supervisor/admin can reopen closed tickets
                    if (! $actor->hasPermission('tickets.reopen')) {
                        throw ValidationException::withMessages([
                            'status' => ['Hanya supervisor atau admin yang dapat membuka kembali tiket yang sudah ditutup.'],
                        ]);
                    }
                } elseif ($from === TicketStatus::Resolved) {
                    // Requester or supervisor can reopen resolved tickets
                    if (! $ticket->isOwnedBy($actor) && ! $actor->hasPermission('tickets.reopen')) {
                        throw ValidationException::withMessages([
                            'status' => ['Hanya pelapor atau supervisor yang dapat membuka kembali tiket yang sudah diselesaikan.'],
                        ]);
                    }
                }
                break;

            case TicketStatus::Cancelled:
                if ($from === TicketStatus::Open) {
                    // Requester or supervisor
                    if (! $ticket->isOwnedBy($actor) && ! $actor->hasPermission('tickets.cancel')) {
                        throw ValidationException::withMessages([
                            'status' => ['Anda tidak memiliki izin untuk membatalkan tiket ini.'],
                        ]);
                    }
                }
                break;
        }
    }

    /**
     * Validate data required for specific status transitions.
     */
    private function validateTransitionData(TicketStatus $newStatus, array $data): void
    {
        switch ($newStatus) {
            case TicketStatus::Pending:
                if (empty($data['pending_reason'])) {
                    throw ValidationException::withMessages([
                        'pending_reason' => ['Alasan pending wajib diisi.'],
                    ]);
                }
                // Validate pending reason is valid enum value
                $validReasons = array_column(PendingReason::cases(), 'value');
                if (! in_array($data['pending_reason'], $validReasons)) {
                    throw ValidationException::withMessages([
                        'pending_reason' => ['Alasan pending tidak valid.'],
                    ]);
                }
                // "other" requires description
                if ($data['pending_reason'] === PendingReason::Other->value && empty($data['pending_note'])) {
                    throw ValidationException::withMessages([
                        'pending_note' => ['Catatan pending wajib diisi jika alasan adalah "Lainnya".'],
                    ]);
                }
                break;

            case TicketStatus::Resolved:
                if (empty($data['resolution_summary'])) {
                    throw ValidationException::withMessages([
                        'resolution_summary' => ['Catatan penyelesaian wajib diisi.'],
                    ]);
                }
                break;

            case TicketStatus::Reopened:
                if (empty($data['reason'])) {
                    throw ValidationException::withMessages([
                        'reason' => ['Alasan pembukaan kembali wajib diisi.'],
                    ]);
                }
                break;

            case TicketStatus::Cancelled:
                if (empty($data['reason'])) {
                    throw ValidationException::withMessages([
                        'reason' => ['Alasan pembatalan wajib diisi.'],
                    ]);
                }
                break;
        }
    }

    /**
     * Get list of target statuses available for transition by a user.
     *
     * @return array<TicketStatus>
     */
    public function getAvailableTransitions(Ticket $ticket, User $user): array
    {
        $currentStatus = $ticket->status;
        $availableTransitions = [];

        foreach ($currentStatus->allowedTransitions() as $targetStatus => $permissions) {
            $target = TicketStatus::from($targetStatus);
            // Check if user has any of the required permissions
            foreach ($permissions as $permission) {
                if ($user->hasPermission($permission)) {
                    $availableTransitions[] = $target;
                    break;
                }
            }
        }

        return $availableTransitions;
    }
}
