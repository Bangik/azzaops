<?php

namespace App\Enums;

enum CustomerType: string
{
    case Individual = 'individual';
    case Business = 'business';

    public function label(): string
    {
        return match ($this) {
            self::Individual => 'Perorangan',
            self::Business => 'Perusahaan',
        };
    }
}
