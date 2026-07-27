<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $from,
        private readonly string $to
    ) {}

    public function query()
    {
        return User::withCount([
            'assignments' => function ($q) {
                $q->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
            },
            'reports' => function ($q) {
                $q->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
            }
        ])->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nama Staff',
            'Email',
            'Telepon',
            'Role',
            'Status Aktif',
            'Jumlah Tugas (WO) (Periode)',
            'Jumlah Laporan Terkirim (Periode)'
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->email,
            $row->phone ?? '-',
            $row->role->label(),
            $row->is_active ? 'Aktif' : 'Nonaktif',
            $row->assignments_count,
            $row->reports_count
        ];
    }
}
