<?php

namespace App\Enums;

enum CommentVisibility: string
{
    case Public = 'public';
    case Internal = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Publik',
            self::Internal => 'Internal',
        };
    }
}
