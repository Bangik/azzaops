<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendancesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
        private readonly ?string $userId = null,
        private readonly ?string $status = null,
    ) {}

    public function query()
    {
        $query = Attendance::with('user')
            ->whereBetween('date', [$this->from, $this->to])
            ->orderBy('date', 'desc')
            ->orderBy('check_in', 'desc');

        if ($this->userId) {
            $query->where('user_id', $this->userId);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Staff',
            'Role',
            'Jam Masuk',
            'Jam Pulang',
            'Status',
            'Durasi Kerja',
            'Catatan',
        ];
    }

    public function map($row): array
    {
        return [
            $row->date->format('d/m/Y'),
            $row->user->name ?? '-',
            $row->user->role->label() ?? '-',
            $row->check_in ?? '-',
            $row->check_out ?? '-',
            $row->status->label(),
            $row->work_duration ?? '-',
            $row->notes ?? '-',
        ];
    }
}
