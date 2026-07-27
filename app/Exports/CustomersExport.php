<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomersExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $from,
        private readonly string $to
    ) {}

    public function query()
    {
        return Customer::withCount(['workOrders' => function ($q) {
                $q->whereBetween('created_at', [$this->from . ' 00:00:00', $this->to . ' 23:59:59']);
            }])
            ->orderBy('name');
    }

    public function headings(): array
    {
        return [
            'Nama Customer',
            'Tipe',
            'Nama Perusahaan',
            'PIC',
            'Telepon',
            'Telepon Alternatif',
            'Email',
            'Kota',
            'Market / Segmen',
            'Alamat',
            'Jumlah WO (Periode)',
            'Catatan'
        ];
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->type->label(),
            $row->company_name ?? '-',
            $row->pic_name ?? '-',
            $row->phone,
            $row->phone_alt ?? '-',
            $row->email ?? '-',
            $row->city ?? '-',
            $row->market ?? '-',
            $row->address ?? '-',
            $row->work_orders_count,
            $row->notes ?? '-'
        ];
    }
}
