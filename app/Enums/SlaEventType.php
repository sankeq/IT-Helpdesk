<?php

namespace App\Enums;

enum SlaEventType: string
{
    case Warning = 'warning';
    case Pause = 'pause';
    case Resume = 'resume';
    case ResponseBreach = 'response_breach';
    case ResolutionBreach = 'resolution_breach';
    case TargetRecalculated = 'target_recalculated';

    public function label(): string
    {
        return match ($this) {
            self::Warning => 'Peringatan SLA (75%)',
            self::Pause => 'SLA Dijeda (Pause)',
            self::Resume => 'SLA Dilanjutkan (Resume)',
            self::ResponseBreach => 'Pelanggaran Respon Pertama (Breach)',
            self::ResolutionBreach => 'Pelanggaran Waktu Penyelesaian (Breach)',
            self::TargetRecalculated => 'Target SLA Dihitung Ulang',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Warning => 'amber',
            self::Pause => 'blue',
            self::Resume => 'indigo',
            self::ResponseBreach, self::ResolutionBreach => 'rose',
            self::TargetRecalculated => 'slate',
        };
    }
}
