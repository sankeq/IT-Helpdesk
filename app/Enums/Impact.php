<?php

namespace App\Enums;

enum Impact: string
{
    case High = 'high';
    case Medium = 'medium';
    case Low = 'low';

    public function label(): string
    {
        return match ($this) {
            self::High => 'Tinggi',
            self::Medium => 'Sedang',
            self::Low => 'Rendah',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::High => 'Berdampak pada seluruh departemen atau operasional galangan',
            self::Medium => 'Berdampak pada unit kerja atau beberapa pengguna',
            self::Low => 'Berdampak pada satu pengguna secara individual',
        };
    }
}
