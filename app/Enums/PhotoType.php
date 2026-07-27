<?php

namespace App\Enums;

enum PhotoType: string
{
    case Before = 'before';
    case Progress = 'progress';
    case After = 'after';

    public function label(): string
    {
        return match ($this) {
            self::Before => 'Sebelum',
            self::Progress => 'Proses',
            self::After => 'Sesudah',
        };
    }
}
