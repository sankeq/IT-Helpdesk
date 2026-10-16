<?php

namespace App\Enums;

enum PendingReason: string
{
    case WaitingRequester = 'waiting_requester';
    case WaitingApproval = 'waiting_approval';
    case WaitingVendor = 'waiting_vendor';
    case WaitingParts = 'waiting_parts';
    case WaitingSchedule = 'waiting_schedule';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WaitingRequester => 'Menunggu Pelapor',
            self::WaitingApproval => 'Menunggu Persetujuan',
            self::WaitingVendor => 'Menunggu Vendor',
            self::WaitingParts => 'Menunggu Suku Cadang',
            self::WaitingSchedule => 'Menunggu Jadwal',
            self::Other => 'Lainnya',
        };
    }
}
