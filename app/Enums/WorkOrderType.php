<?php

namespace App\Enums;

enum WorkOrderType: string
{
    case Checking = 'checking';
    case Service = 'service';
    case Installation = 'installation';
    case Maintenance = 'maintenance';

    public function label(): string
    {
        return match ($this) {
            self::Checking => 'Pengecekan',
            self::Service => 'Servis/Perbaikan',
            self::Installation => 'Instalasi',
            self::Maintenance => 'Perawatan',
        };
    }
}
