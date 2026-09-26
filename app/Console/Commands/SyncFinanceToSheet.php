<?php

namespace App\Console\Commands;

use App\Models\FinancialTransaction;
use App\Services\GoogleSheetService;
use Illuminate\Console\Command;

class SyncFinanceToSheet extends Command
{
    protected $signature = 'sync:finance-sheet {--start= : Tanggal mulai (Y-m-d)} {--end= : Tanggal akhir (Y-m-d)}';
    protected $description = 'Sync data Keuangan (joined) ke Google Spreadsheet berdasarkan rentang tanggal';

    public function handle(GoogleSheetService $sheetService): int
    {
        $spreadsheetId = config('services.google.spreadsheet_id');

        if (empty($spreadsheetId)) {
            $this->error('GOOGLE_SPREADSHEET_ID belum diset di .env');
            return self::FAILURE;
        }

        $startDate = $this->option('start');
        $endDate = $this->option('end');

        $sheetName = 'Keuangan';
        if ($startDate && $endDate) {
            $this->info("Mengambil data keuangan dari $startDate sampai $endDate...");
        } else {
            $this->info('Mengambil seluruh data keuangan...');
        }

        // ============================================================
        // CUSTOM COLUMNS
        // ============================================================
        $headers = [
            'ID Transaksi',
            'Tanggal',
            'Tipe',
            'Kategori',
            'Akun Keuangan',
            'Nominal',
            'Deskripsi',
            'No Referensi',
            'Pencatat',
            'No Invoice',
            'Status Invoice',
            'Customer',
            'No WO',
            'Judul WO',
            'PIC Pengeluaran',
            'Tgl Buat Data',
        ];

        // ============================================================
        // QUERY — eager load semua relasi yang dibutuhkan
        // ============================================================
        $query = FinancialTransaction::with([
            'category',
            'financialAccount',
            'recorder',
            'invoice.workOrder.customer',
            'invoice.customer',
            'expense.workOrder',
        ]);

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                              ->orderBy('created_at', 'desc')
                              ->get();

        // ============================================================
        // MAP DATA
        // ============================================================
        $rows = $transactions->map(function (FinancialTransaction $tx) {
            
            // Ambil relasi tergantung tipe transaksi (income dari invoice atau expense)
            $inv = $tx->invoice;
            $exp = $tx->expense;
            
            // Resolve nested relationships dengan fallback yang aman
            $woNumber = $inv?->workOrder?->wo_number ?? $exp?->workOrder?->wo_number ?? '';
            $woTitle = $inv?->workOrder?->title ?? $exp?->workOrder?->title ?? '';
            
            $customerName = $inv?->customer?->name ?? $inv?->workOrder?->customer?->name ?? '';
            
            $pic = $exp?->pic ?? '';

            // Gunakan string kosong '' alih-alih null
            return [
                $tx->id,
                $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '',
                $tx->type instanceof \BackedEnum ? $tx->type->value : ($tx->type ?? ''),
                $tx->category?->name ?? '',
                $tx->financialAccount?->name ?? '',
                (float) $tx->amount,
                strip_tags($tx->description ?? ''),
                $tx->reference_number ?? '',
                $tx->recorder?->name ?? '',
                $inv?->invoice_number ?? '',
                $inv?->status instanceof \BackedEnum ? $inv->status->value : ($inv?->status ?? ''),
                $customerName,
                $woNumber,
                $woTitle,
                $pic,
                $tx->created_at ? $tx->created_at->format('Y-m-d H:i') : '',
            ];
        })->toArray();

        $this->info("Total: {$transactions->count()} transaksi");
        $this->info('Membuat sheet jika belum ada...');

        $sheetService->ensureSheetExists($spreadsheetId, $sheetName);

        $this->info('Mengirim data ke Google Spreadsheet...');

        $count = $sheetService->syncSheet($spreadsheetId, $sheetName, $headers, $rows);

        $this->info("Selesai! {$count} baris data keuangan berhasil disync.");

        return self::SUCCESS;
    }
}

