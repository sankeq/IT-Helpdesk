<?php

namespace App\Models;

use App\Enums\CommentVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    use HasFactory;

    protected $fillable = ['ticket_id', 'user_id', 'body', 'visibility'];

    protected function casts(): array
    {
        return [
            'visibility' => CommentVisibility::class,
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isInternal(): bool
    {
        return $this->visibility === CommentVisibility::Internal;
    }

    public function isPublic(): bool
    {
        return $this->visibility === CommentVisibility::Public;
    }
}
