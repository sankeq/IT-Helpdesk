<?php

namespace App\Models;

use App\Enums\Priority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'priority',
        'first_response_minutes',
        'resolution_minutes',
        'business_calendar_id',
        'pause_on_pending_reasons',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'priority' => Priority::class,
            'first_response_minutes' => 'integer',
            'resolution_minutes' => 'integer',
            'pause_on_pending_reasons' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function businessCalendar(): BelongsTo
    {
        return $this->belongsTo(BusinessCalendar::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    /**
     * Check if this policy allows SLA pause on the given pending reason.
     */
    public function shouldPauseOn(?string $reason): bool
    {
        if (! $reason) {
            return false;
        }

        $allowedReasons = $this->pause_on_pending_reasons ?? [
            'waiting_vendor',
            'waiting_parts',
            'waiting_approval',
        ];

        return in_array($reason, $allowedReasons, true);
    }
}
