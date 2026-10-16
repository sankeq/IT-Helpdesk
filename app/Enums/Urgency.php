<?php

namespace App\Enums;

enum Urgency: string
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
            self::High => 'Pekerjaan terhenti total, butuh penanganan segera',
            self::Medium => 'Pekerjaan terganggu tapi masih ada cara alternatif',
            self::Low => 'Dapat ditangani sesuai jadwal antrean normal',
        };
    }
}
