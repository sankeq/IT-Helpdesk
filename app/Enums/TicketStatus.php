<?php

namespace App\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Pending = 'pending';
    case Resolved = 'resolved';
    case Reopened = 'reopened';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Open',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::Pending => 'Pending',
            self::Resolved => 'Resolved',
            self::Reopened => 'Reopened',
            self::Closed => 'Closed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Open => 'blue',
            self::Assigned => 'indigo',
            self::InProgress => 'yellow',
            self::Pending => 'orange',
            self::Resolved => 'green',
            self::Reopened => 'red',
            self::Closed => 'gray',
            self::Cancelled => 'gray',
        };
    }

    /**
     * Get allowed transitions from this status.
     * Returns array of [target_status => required_permissions].
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [
                self::Assigned->value => ['tickets.assign'],
                self::Pending->value => ['tickets.update', 'tickets.assign'],
                self::Cancelled->value => ['tickets.cancel'],
            ],
            self::Assigned => [
                self::InProgress->value => ['tickets.update'],
                self::Pending->value => ['tickets.update'],
                self::Cancelled->value => ['tickets.cancel'],
            ],
            self::InProgress => [
                self::Pending->value => ['tickets.update'],
                self::Resolved->value => ['tickets.resolve'],
                self::Assigned->value => ['tickets.assign'],
            ],
            self::Pending => [
                self::InProgress->value => ['tickets.update'],
                self::Assigned->value => ['tickets.assign'],
                self::Cancelled->value => ['tickets.cancel', 'approvals.decide'],
            ],
            self::Resolved => [
                self::Closed->value => ['tickets.close'],
                self::Reopened->value => ['tickets.reopen'],
            ],
            self::Closed => [
                self::Reopened->value => ['tickets.reopen'],
            ],
            self::Reopened => [
                self::InProgress->value => ['tickets.update'],
                self::Assigned->value => ['tickets.assign'],
            ],
            self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return array_key_exists($target->value, $this->allowedTransitions());
    }
}
