<?php

namespace App\Exports;

use App\Models\Rab;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RabsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
        private readonly ?string $status
    ) {}

    public function query()
    {
        $query = Rab::with(['customer', 'workOrder'])
            ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'No. RAB',
            'No. Work Order',
            'Nama Customer',
            'Judul Proyek',
            'Subtotal (IDR)',
            'Diskon (IDR)',
            'Pajak (IDR)',
            'Total (IDR)',
            'Status RAB',
            'Masa Berlaku',
            'Disetujui Tanggal'
        ];
    }

    public function map($row): array
    {
        return [
            $row->rab_number,
            $row->workOrder->wo_number,
            $row->customer->name,
            $row->title,
            $row->subtotal,
            $row->discount,
            $row->tax_amount,
            $row->total,
            $row->status->label(),
            $row->valid_until ? $row->valid_until->format('d/m/Y') : '-',
            $row->approved_at ? $row->approved_at->format('d/m/Y H:i') : '-'
        ];
    }
}
