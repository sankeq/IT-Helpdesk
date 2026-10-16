<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id', 'code', 'name', 'description',
        'requires_approval', 'is_security_sensitive',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'requires_approval' => 'boolean',
            'is_security_sensitive' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id');
    }

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    public function requiresApproval(): bool
    {
        if ($this->requires_approval) {
            return true;
        }

        if ($this->relationLoaded('parent') ? $this->parent : $this->parent()->first()) {
            return (bool) ($this->parent?->requires_approval ?? false);
        }

        return false;
    }

    public function isSecuritySensitive(): bool
    {
        if ($this->is_security_sensitive) {
            return true;
        }

        if ($this->relationLoaded('parent') ? $this->parent : $this->parent()->first()) {
            return (bool) ($this->parent?->is_security_sensitive ?? false);
        }

        return false;
    }
}
