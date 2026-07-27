<?php

namespace App\Exports;

use App\Models\FinancialTransaction;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinanceExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private readonly string $from,
        private readonly string $to,
        private readonly ?string $type
    ) {}

    public function query()
    {
        $query = FinancialTransaction::with(['category', 'invoice', 'expense', 'recorder'])
            ->whereBetween('transaction_date', [$this->from, $this->to]);

        if ($this->type) {
            $query->where('type', $this->type);
        }

        return $query->latest('transaction_date');
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Tipe',
            'Kategori',
            'Nominal (IDR)',
            'Deskripsi',
            'No. Referensi',
            'Dicatat Oleh'
        ];
    }

    public function map($row): array
    {
        return [
            $row->transaction_date->format('d/m/Y'),
            $row->type->label(),
            $row->category ? $row->category->name : ($row->type->value === 'income' ? 'Pembayaran Jasa' : 'Lainnya'),
            $row->amount,
            $row->description,
            $row->reference_number ?? '-',
            $row->recorder->name
        ];
    }
}
