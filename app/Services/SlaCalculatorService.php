<?php

namespace App\Services;

use App\Enums\SlaEventType;
use App\Enums\TicketStatus;
use App\Models\SlaPolicy;
use App\Models\Ticket;
use App\Models\TicketSlaEvent;
use App\Models\User;
use App\Notifications\SlaBreachNotification;
use App\Notifications\SlaWarningNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class SlaCalculatorService
{
    /**
     * Apply SLA policy and calculate target dates upon ticket creation.
     */
    public function applySla(Ticket $ticket): void
    {
        $priority = $ticket->priority;
        $policy = SlaPolicy::where('priority', $priority->value)
            ->where('is_active', true)
            ->with('businessCalendar')
            ->first();

        if (! $policy) {
            return;
        }

        $calendar = $policy->businessCalendar;
        $snapshot = [
            'policy_id' => $policy->id,
            'policy_name' => $policy->name,
            'priority' => $policy->priority->value,
            'first_response_minutes' => $policy->first_response_minutes,
            'resolution_minutes' => $policy->resolution_minutes,
            'calendar_id' => $calendar?->id,
            'calendar_name' => $calendar?->name,
            'timezone' => $calendar?->timezone ?? 'Asia/Makassar',
            'work_start_time' => $calendar?->work_start_time ?? '08:00',
            'work_end_time' => $calendar?->work_end_time ?? '17:00',
            'work_days' => $calendar?->work_days ?? [1, 2, 3, 4, 5],
            'holidays' => $calendar?->holidays ?? [],
            'pause_on_pending_reasons' => $policy->pause_on_pending_reasons ?? [
                'waiting_vendor',
                'waiting_parts',
                'waiting_approval',
            ],
        ];

        $createdAt = $ticket->created_at ?? now();

        if ($calendar && $calendar->is_active) {
            $firstResponseDueAt = $calendar->addBusinessMinutes($createdAt, $policy->first_response_minutes);
            $resolutionDueAt = $calendar->addBusinessMinutes($createdAt, $policy->resolution_minutes);
        } else {
            $firstResponseDueAt = $createdAt->copy()->addMinutes($policy->first_response_minutes);
            $resolutionDueAt = $createdAt->copy()->addMinutes($policy->resolution_minutes);
        }

        $ticket->update([
            'sla_policy_id' => $policy->id,
            'sla_policy_snapshot' => $snapshot,
            'first_response_due_at' => $firstResponseDueAt,
            'resolution_due_at' => $resolutionDueAt,
            'is_sla_paused' => false,
            'total_paused_seconds' => 0,
        ]);

        $ticket->slaEvents()->create([
            'event_type' => SlaEventType::TargetRecalculated,
            'occurred_at' => now(),
            'metadata' => [
                'policy_name' => $policy->name,
                'first_response_due_at' => $firstResponseDueAt->toIso8601String(),
                'resolution_due_at' => $resolutionDueAt->toIso8601String(),
            ],
            'idempotency_key' => "ticket-{$ticket->id}-sla-applied",
        ]);
    }

    /**
     * Handle SLA pause/resume when ticket status transitions.
     */
    public function handleStatusTransition(
        Ticket $ticket,
        TicketStatus $from,
        TicketStatus $to,
        ?string $pendingReason = null,
        ?string $note = null
    ): void {
        // 1. Transition into Pending -> check if pause applies
        if ($to === TicketStatus::Pending) {
            $snapshotReasons = $ticket->sla_policy_snapshot['pause_on_pending_reasons'] ?? [
                'waiting_vendor',
                'waiting_parts',
                'waiting_approval',
            ];

            if ($pendingReason && in_array($pendingReason, $snapshotReasons, true) && ! $ticket->is_sla_paused) {
                $ticket->update([
                    'is_sla_paused' => true,
                    'last_paused_at' => now(),
                ]);

                $ticket->slaEvents()->create([
                    'event_type' => SlaEventType::Pause,
                    'occurred_at' => now(),
                    'metadata' => [
                        'reason' => $pendingReason,
                        'note' => $note,
                    ],
                ]);
            }
        }

        // 2. Transition out of Pending -> resume and extend due dates if paused
        if ($from === TicketStatus::Pending && $to !== TicketStatus::Pending) {
            if ($ticket->is_sla_paused) {
                $pausedSeconds = $ticket->last_paused_at
                    ? abs((int) now()->diffInSeconds($ticket->last_paused_at))
                    : 0;
                $newTotalPaused = ($ticket->total_paused_seconds ?? 0) + $pausedSeconds;
                $pausedMinutes = (int) ceil($pausedSeconds / 60);

                $calendar = $ticket->slaPolicy?->businessCalendar;

                $newResolutionDue = $ticket->resolution_due_at;
                if ($newResolutionDue && $pausedMinutes > 0) {
                    $newResolutionDue = ($calendar && $calendar->is_active)
                        ? $calendar->addBusinessMinutes($newResolutionDue, $pausedMinutes)
                        : $newResolutionDue->copy()->addSeconds($pausedSeconds);
                }

                $newFirstResponseDue = $ticket->first_response_due_at;
                if ($newFirstResponseDue && ! $ticket->first_responded_at && $pausedMinutes > 0) {
                    $newFirstResponseDue = ($calendar && $calendar->is_active)
                        ? $calendar->addBusinessMinutes($newFirstResponseDue, $pausedMinutes)
                        : $newFirstResponseDue->copy()->addSeconds($pausedSeconds);
                }

                $ticket->update([
                    'is_sla_paused' => false,
                    'last_paused_at' => null,
                    'total_paused_seconds' => $newTotalPaused,
                    'resolution_due_at' => $newResolutionDue,
                    'first_response_due_at' => $newFirstResponseDue,
                ]);

                $ticket->slaEvents()->create([
                    'event_type' => SlaEventType::Resume,
                    'occurred_at' => now(),
                    'duration_seconds' => $pausedSeconds,
                    'metadata' => [
                        'resumed_to_status' => $to->value,
                        'new_resolution_due_at' => $newResolutionDue?->toIso8601String(),
                        'new_first_response_due_at' => $newFirstResponseDue?->toIso8601String(),
                    ],
                ]);
            }
        }
    }

    /**
     * Record first response on the ticket when an agent takes public action.
     */
    public function recordFirstResponse(Ticket $ticket, ?User $actor = null): void
    {
        if ($ticket->first_responded_at !== null) {
            return;
        }

        // Only agent/technician/admin actions count as first response, not requester
        if ($actor && $actor->id === $ticket->requester_id) {
            return;
        }

        $now = now();
        $ticket->update(['first_responded_at' => $now]);

        // Check if first response breached
        if ($ticket->first_response_due_at && $now->gt($ticket->first_response_due_at)) {
            $idempotencyKey = "ticket-{$ticket->id}-first-response-breach";
            if (! TicketSlaEvent::where('idempotency_key', $idempotencyKey)->exists()) {
                $ticket->slaEvents()->create([
                    'event_type' => SlaEventType::ResponseBreach,
                    'occurred_at' => $now,
                    'metadata' => [
                        'first_response_due_at' => $ticket->first_response_due_at->toIso8601String(),
                        'first_responded_at' => $now->toIso8601String(),
                    ],
                    'idempotency_key' => $idempotencyKey,
                ]);

                $this->sendBreachNotification($ticket, 'first_response');
            }
        }
    }

    /**
     * Check for SLA breaches and warnings across active tickets.
     * Idempotent: alerts are triggered only once per threshold per ticket.
     *
     * @return array Summary of processed events
     */
    public function checkBreachesAndWarnings(): array
    {
        $activeStatuses = [
            TicketStatus::Open,
            TicketStatus::Assigned,
            TicketStatus::InProgress,
            TicketStatus::Pending,
        ];

        $tickets = Ticket::whereIn('status', $activeStatuses)
            ->whereNotNull('sla_policy_snapshot')
            ->get();

        $stats = [
            'warnings_sent' => 0,
            'breaches_sent' => 0,
            'tickets_scanned' => $tickets->count(),
        ];

        $now = now();

        foreach ($tickets as $ticket) {
            // While SLA is paused, timers do not advance towards breach/warning
            if ($ticket->is_sla_paused) {
                continue;
            }

            // 1. Check First Response SLA
            if (! $ticket->first_responded_at && $ticket->first_response_due_at) {
                if ($now->gt($ticket->first_response_due_at)) {
                    $key = "ticket-{$ticket->id}-first-response-breach";
                    if (! TicketSlaEvent::where('idempotency_key', $key)->exists()) {
                        $ticket->slaEvents()->create([
                            'event_type' => SlaEventType::ResponseBreach,
                            'occurred_at' => $now,
                            'metadata' => [
                                'due_at' => $ticket->first_response_due_at->toIso8601String(),
                                'now' => $now->toIso8601String(),
                            ],
                            'idempotency_key' => $key,
                        ]);
                        $this->sendBreachNotification($ticket, 'first_response');
                        $stats['breaches_sent']++;
                    }
                } else {
                    // Check 75% warning for First Response
                    $totalDuration = max(1, $ticket->created_at->diffInSeconds($ticket->first_response_due_at));
                    $remaining = $now->diffInSeconds($ticket->first_response_due_at, false);
                    $elapsedRatio = 1 - ($remaining / $totalDuration);

                    if ($elapsedRatio >= 0.75) {
                        $key = "ticket-{$ticket->id}-first-response-warning-75";
                        if (! TicketSlaEvent::where('idempotency_key', $key)->exists()) {
                            $ticket->slaEvents()->create([
                                'event_type' => SlaEventType::Warning,
                                'occurred_at' => $now,
                                'metadata' => [
                                    'target' => 'first_response',
                                    'percentage' => 75,
                                    'due_at' => $ticket->first_response_due_at->toIso8601String(),
                                ],
                                'idempotency_key' => $key,
                            ]);
                            $this->sendWarningNotification($ticket, 'first_response', 75);
                            $stats['warnings_sent']++;
                        }
                    }
                }
            }

            // 2. Check Resolution SLA
            if ($ticket->resolution_due_at) {
                if ($now->gt($ticket->resolution_due_at)) {
                    $key = "ticket-{$ticket->id}-resolution-breach";
                    if (! TicketSlaEvent::where('idempotency_key', $key)->exists()) {
                        $ticket->slaEvents()->create([
                            'event_type' => SlaEventType::ResolutionBreach,
                            'occurred_at' => $now,
                            'metadata' => [
                                'due_at' => $ticket->resolution_due_at->toIso8601String(),
                                'now' => $now->toIso8601String(),
                            ],
                            'idempotency_key' => $key,
                        ]);
                        $this->sendBreachNotification($ticket, 'resolution');
                        $stats['breaches_sent']++;
                    }
                } else {
                    // Check 75% warning for Resolution
                    $totalDuration = max(1, $ticket->created_at->diffInSeconds($ticket->resolution_due_at));
                    $remaining = $now->diffInSeconds($ticket->resolution_due_at, false);
                    $elapsedRatio = 1 - ($remaining / $totalDuration);

                    if ($elapsedRatio >= 0.75) {
                        $key = "ticket-{$ticket->id}-resolution-warning-75";
                        if (! TicketSlaEvent::where('idempotency_key', $key)->exists()) {
                            $ticket->slaEvents()->create([
                                'event_type' => SlaEventType::Warning,
                                'occurred_at' => $now,
                                'metadata' => [
                                    'target' => 'resolution',
                                    'percentage' => 75,
                                    'due_at' => $ticket->resolution_due_at->toIso8601String(),
                                ],
                                'idempotency_key' => $key,
                            ]);
                            $this->sendWarningNotification($ticket, 'resolution', 75);
                            $stats['warnings_sent']++;
                        }
                    }
                }
            }
        }

        return $stats;
    }

    /**
     * Send breach notification to assignee and escalation supervisors.
     */
    protected function sendBreachNotification(Ticket $ticket, string $targetType): void
    {
        $recipients = $this->getNotificationRecipients($ticket);
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new SlaBreachNotification($ticket, $targetType));
        }
    }

    /**
     * Send warning notification to assignee and escalation supervisors.
     */
    protected function sendWarningNotification(Ticket $ticket, string $targetType, int $percentage): void
    {
        $recipients = $this->getNotificationRecipients($ticket);
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new SlaWarningNotification($ticket, $targetType, $percentage));
        }
    }

    /**
     * Get users who should be notified (assignee + users with tickets.assign permission).
     */
    public function getNotificationRecipients(Ticket $ticket): Collection
    {
        $recipients = collect();

        if ($ticket->assignee) {
            $recipients->push($ticket->assignee);
        }

        // Also fetch supervisors/admins who have ticket assignment permission
        $supervisors = User::where('is_active', true)
            ->whereHas('roles.permissions', function ($query) {
                $query->where('name', 'tickets.assign');
            })
            ->get();

        return $recipients->merge($supervisors)->unique('id');
    }
}
