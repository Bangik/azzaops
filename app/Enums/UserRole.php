<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case KepalaTeknisi = 'kepala_teknisi';
    case Teknisi = 'teknisi';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::KepalaTeknisi => 'Kepala Teknisi',
            self::Teknisi => 'Teknisi',
        };
    }
}
