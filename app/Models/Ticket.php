<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use App\Enums\CommentVisibility;
use App\Enums\Impact;
use App\Enums\PendingReason;
use App\Enums\Priority;
use App\Enums\TicketStatus;
use App\Enums\Urgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number', 'requester_id', 'assignee_id', 'category_id',
        'location_id', 'asset_id', 'subject', 'description', 'impact', 'urgency',
        'priority', 'status', 'source', 'pending_reason', 'pending_note',
        'resolution_summary', 'resolved_at', 'closed_at',
        'rating', 'rating_comment', 'rated_at',
        'sla_policy_id', 'sla_policy_snapshot', 'first_response_due_at',
        'resolution_due_at', 'first_responded_at', 'total_paused_seconds',
        'last_paused_at', 'is_sla_paused',
    ];

    protected function casts(): array
    {
        return [
            'status' => TicketStatus::class,
            'priority' => Priority::class,
            'impact' => Impact::class,
            'urgency' => Urgency::class,
            'pending_reason' => PendingReason::class,
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
            'rating' => 'integer',
            'rated_at' => 'datetime',
            'sla_policy_snapshot' => 'array',
            'first_response_due_at' => 'datetime',
            'resolution_due_at' => 'datetime',
            'first_responded_at' => 'datetime',
            'last_paused_at' => 'datetime',
            'is_sla_paused' => 'boolean',
            'total_paused_seconds' => 'integer',
        ];
    }

    // ─── Relationships ───

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(TicketCategory::class, 'category_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(Asset::class, 'ticket_assets');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(TicketComment::class)
            ->where('visibility', CommentVisibility::Public);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(TicketHistory::class)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(Approval::class)->orderBy('created_at', 'desc');
    }

    public function latestApproval()
    {
        return $this->hasOne(Approval::class)->latestOfMany();
    }

    public function hasPendingApproval(): bool
    {
        return $this->approvals()->where('status', ApprovalStatus::Pending)->exists();
    }

    public function slaPolicy(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class);
    }

    public function slaEvents(): HasMany
    {
        return $this->hasMany(TicketSlaEvent::class)->orderBy('occurred_at', 'asc');
    }

    // ─── Scopes ───

    /**
     * Scope tickets visible to a specific user based on their permissions.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->hasPermission('tickets.view_all')) {
            return $query;
        }

        if ($user->hasPermission('tickets.view_assigned')) {
            return $query->where(function ($q) use ($user) {
                $q->where('requester_id', $user->id)
                    ->orWhere('assignee_id', $user->id);
            });
        }

        // Default: only own tickets
        return $query->where('requester_id', $user->id);
    }

    public function scopeFilterStatus($query, ?string $status)
    {
        if ($status) {
            return $query->where('status', $status);
        }

        return $query;
    }

    public function scopeFilterPriority($query, ?string $priority)
    {
        if ($priority) {
            return $query->where('priority', $priority);
        }

        return $query;
    }

    public function scopeFilterCategory($query, ?int $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }

        return $query;
    }

    public function scopeFilterDateRange($query, ?string $from, ?string $to)
    {
        if ($from) {
            $query->where('created_at', '>=', $from);
        }
        if ($to) {
            $query->where('created_at', '<=', $to.' 23:59:59');
        }

        return $query;
    }

    public function scopeSearch($query, ?string $search)
    {
        if ($search) {
            return $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    // ─── Helper methods ───

    public function isOwnedBy(User $user): bool
    {
        return $this->requester_id === $user->id;
    }

    public function isAssignedTo(User $user): bool
    {
        return $this->assignee_id === $user->id;
    }

    public function isFirstResponseBreached(): bool
    {
        if (! $this->first_response_due_at) {
            return false;
        }

        if ($this->first_responded_at) {
            return $this->first_responded_at->gt($this->first_response_due_at);
        }

        // If not responded yet and ticket is not closed/resolved
        if ($this->status !== TicketStatus::Resolved && $this->status !== TicketStatus::Closed) {
            return now()->gt($this->first_response_due_at);
        }

        return false;
    }

    public function isResolutionBreached(): bool
    {
        if (! $this->resolution_due_at) {
            return false;
        }

        if ($this->resolved_at) {
            return $this->resolved_at->gt($this->resolution_due_at);
        }

        if ($this->status !== TicketStatus::Resolved && $this->status !== TicketStatus::Closed) {
            return now()->gt($this->resolution_due_at);
        }

        return false;
    }

    public function isSlaBreached(): bool
    {
        return $this->isFirstResponseBreached() || $this->isResolutionBreached();
    }

    public function slaStatusBadge(): array
    {
        if ($this->is_sla_paused) {
            return [
                'label' => 'SLA Dijeda',
                'bg' => 'bg-amber-500/10 dark:bg-amber-500/20',
                'text' => 'text-amber-600 dark:text-amber-400',
                'border' => 'border-amber-500/20',
            ];
        }

        if ($this->isSlaBreached()) {
            return [
                'label' => 'SLA Breached',
                'bg' => 'bg-rose-500/10 dark:bg-rose-500/20',
                'text' => 'text-rose-600 dark:text-rose-400',
                'border' => 'border-rose-500/20',
            ];
        }

        if ($this->status === TicketStatus::Resolved || $this->status === TicketStatus::Closed) {
            return [
                'label' => 'SLA Terpenuhi',
                'bg' => 'bg-emerald-500/10 dark:bg-emerald-500/20',
                'text' => 'text-emerald-600 dark:text-emerald-400',
                'border' => 'border-emerald-500/20',
            ];
        }

        return [
            'label' => 'SLA Aktif',
            'bg' => 'bg-sky-500/10 dark:bg-sky-500/20',
            'text' => 'text-sky-600 dark:text-sky-400',
            'border' => 'border-sky-500/20',
        ];
    }

    public function isRated(): bool
    {
        return ! is_null($this->rating);
    }

    public function canBeRatedBy(User $user): bool
    {
        return $this->isOwnedBy($user)
            && ($this->status === TicketStatus::Resolved || $this->status === TicketStatus::Closed)
            && ! $this->isRated();
    }
}
