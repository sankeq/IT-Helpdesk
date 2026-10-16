<?php

namespace App\Models;

use App\Enums\HistoryEventType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketHistory extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'ticket_id', 'actor_id', 'event_type',
        'old_value', 'new_value', 'reason', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'event_type' => HistoryEventType::class,
            'old_value' => 'array',
            'new_value' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
