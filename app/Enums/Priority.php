<?php

namespace App\Enums;

enum Priority: string
{
    case P1 = 'P1';
    case P2 = 'P2';
    case P3 = 'P3';
    case P4 = 'P4';

    public function label(): string
    {
        return match ($this) {
            self::P1 => 'P1 - Critical',
            self::P2 => 'P2 - High',
            self::P3 => 'P3 - Medium',
            self::P4 => 'P4 - Low',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::P1 => 'red',
            self::P2 => 'orange',
            self::P3 => 'yellow',
            self::P4 => 'green',
        };
    }

    /**
     * Calculate priority from impact and urgency using the matrix.
     * Matrix from WORKFLOW_SPEC.md Section 5.
     */
    public static function fromMatrix(Impact $impact, Urgency $urgency): self
    {
        $matrix = [
            Impact::High->value => [
                Urgency::High->value => self::P1,
                Urgency::Medium->value => self::P2,
                Urgency::Low->value => self::P2,
            ],
            Impact::Medium->value => [
                Urgency::High->value => self::P2,
                Urgency::Medium->value => self::P3,
                Urgency::Low->value => self::P3,
            ],
            Impact::Low->value => [
                Urgency::High->value => self::P3,
                Urgency::Medium->value => self::P4,
                Urgency::Low->value => self::P4,
            ],
        ];

        return $matrix[$impact->value][$urgency->value];
    }
}
