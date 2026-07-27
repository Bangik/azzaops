<?php

namespace App\Exports;

use App\Models\Invoice;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
        private readonly ?string $status
    ) {}

    public function query()
    {
        $query = Invoice::with(['customer', 'workOrder'])
            ->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest();
    }

    public function headings(): array
    {
        return [
            'No. Invoice',
            'No. Work Order',
            'Nama Customer',
            'Subtotal (IDR)',
            'Diskon (IDR)',
            'Pajak (IDR)',
            'Total Tagihan (IDR)',
            'Status Tagihan',
            'Status Bayar',
            'Jumlah Dibayar (IDR)',
            'Tanggal Pembayaran',
            'Metode Pembayaran'
        ];
    }

    public function map($row): array
    {
        return [
            $row->invoice_number,
            $row->workOrder->wo_number,
            $row->customer->name,
            $row->subtotal,
            $row->discount,
            $row->tax_amount,
            $row->total,
            $row->status->label(),
            $row->payment_status->label(),
            $row->paid_amount,
            $row->payment_date ? $row->payment_date->format('d/m/Y') : '-',
            $row->payment_method ?? '-'
        ];
    }
}
