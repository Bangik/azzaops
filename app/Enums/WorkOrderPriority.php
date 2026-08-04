<?php

namespace App\Enums;

enum WorkOrderPriority: string
{
    case Low = '4';
    case Normal = '3';
    case High = '2';
    case Urgent = '1';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Prioritas 4 (Rendah)',
            self::Normal => 'Prioritas 3 (Normal)',
            self::High => 'Prioritas 2 (Tinggi)',
            self::Urgent => 'Prioritas 1 (Urgent)',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Low => 'secondary',
            self::Normal => 'info',
            self::High => 'warning',
            self::Urgent => 'danger',
        };
    }
}
