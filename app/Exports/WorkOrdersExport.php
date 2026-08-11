<?php

namespace App\Exports;

use App\Models\WorkOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WorkOrdersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
        private readonly ?int $technicianId,
        private readonly ?string $status
    ) {}

    public function query()
    {
        $query = WorkOrder::with(['customer', 'serviceCategory', 'type', 'assignments.technician'])
            ->whereBetween('scheduled_date', [$this->from, $this->to]);

        if ($this->technicianId) {
            $query->whereHas('assignments', function ($q) {
                $q->where('technician_id', $this->technicianId);
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest('scheduled_date');
    }

    public function headings(): array
    {
        return [
            'No. WO',
            'Judul Pekerjaan',
            'Tipe Pekerjaan',
            'Kategori Layanan',
            'Nama Customer',
            'Perusahaan Customer',
            'Alamat Pengerjaan',
            'Tanggal Rencana',
            'Teknisi Terkait',
            'Status WO',
            'Total Nilai (IDR)',
            'Tanggal Selesai'
        ];
    }

    public function map($row): array
    {
        $techs = $row->assignments->map(fn($a) => $a->technician->name)->join(', ');
        return [
            $row->wo_number,
            $row->title,
            $row->type->name,
            $row->serviceCategory->name,
            $row->customer->name,
            $row->customer->company_name ?? '-',
            $row->location,
            $row->scheduled_date ? $row->scheduled_date->format('d/m/Y') : '-',
            $techs ?: 'Belum ditugaskan',
            $row->status->label(),
            $row->total,
            $row->completed_at ? $row->completed_at->format('d/m/Y H:i') : '-'
        ];
    }
}
