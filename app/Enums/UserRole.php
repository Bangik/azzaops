<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case KepalaTeknisi = 'kepala_teknisi';
    case Teknisi = 'teknisi';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Supervisor => 'Supervisor',
            self::KepalaTeknisi => 'Kepala Teknisi',
            self::Teknisi => 'Teknisi',
        };
    }
}
