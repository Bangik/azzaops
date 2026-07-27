<?php

namespace App\Enums;

enum RabStatus: string
{
    case Draft = 'draft';
    case Sent = 'sent';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Revised = 'revised';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::Sent => 'Terkirim',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
            self::Revised => 'Direvisi',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Sent => 'info',
            self::Approved => 'success',
            self::Rejected => 'danger',
            self::Revised => 'warning',
        };
    }
}
