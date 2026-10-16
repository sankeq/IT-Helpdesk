<?php

namespace App\Models;

use App\Enums\SlaEventType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketSlaEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'event_type',
        'occurred_at',
        'duration_seconds',
        'metadata',
        'idempotency_key',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => SlaEventType::class,
            'occurred_at' => 'datetime',
            'duration_seconds' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }
}
