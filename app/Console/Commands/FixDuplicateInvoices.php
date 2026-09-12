<?php

namespace App\Console\Commands;

use App\Models\FinancialTransaction;
use App\Models\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixDuplicateInvoices extends Command
{
    protected $signature = 'invoices:fix-duplicates {--dry-run : Hanya tampilkan laporan tanpa mengubah data}';
    protected $description = 'Cek & perbaiki invoice ganda per work order sebelum menjalankan migration unique constraint';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $duplicateWoIds = Invoice::query()
            ->select('work_order_id')
            ->whereNotNull('work_order_id')
            ->groupBy('work_order_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('work_order_id');

        if ($duplicateWoIds->isEmpty()) {
            $this->info('Tidak ditemukan invoice ganda. Aman untuk migrate.');
            return self::SUCCESS;
        }

        $this->warn("Ditemukan {$duplicateWoIds->count()} work order dengan invoice ganda.");

        foreach ($duplicateWoIds as $woId) {
            $invoices = Invoice::withCount('items')
                ->where('work_order_id', $woId)
                ->orderBy('id')
                ->get();

            $this->newLine();
            $this->line("<comment>Work Order #{$woId}</comment> ({$invoices->first()?->workOrder?->wo_number})");
            $this->table(
                ['ID', 'Nomor Invoice', 'Status', 'Bayar', 'Total', 'Items', 'Transaksi Terkait', 'Dibuat'],
                $invoices->map(fn(Invoice $inv) => [
                    $inv->id,
                    $inv->invoice_number,
                    $inv->status->value,
                    $inv->payment_status->value,
                    number_format($inv->total, 0, ',', '.'),
                    $inv->items_count,
                    FinancialTransaction::where('invoice_id', $inv->id)->count(),
                    $inv->created_at,
                ])->all()
            );

            if ($dryRun) {
                continue;
            }

            $keepId = (int) $this->ask('Masukkan ID invoice yang ingin DIPERTAHANKAN (invoice lain akan dihapus)');
            $keep = $invoices->firstWhere('id', $keepId);

            if (! $keep) {
                $this->error('ID tidak valid, WO ini dilewati. Jalankan ulang command untuk mencoba lagi.');
                continue;
            }

            if (! $this->confirm("Yakin hapus " . ($invoices->count() - 1) . " invoice lain untuk WO #{$woId} dan pindahkan transaksi keuangannya ke invoice #{$keep->id}?")) {
                $this->line('Dilewati.');
                continue;
            }

            DB::transaction(function () use ($invoices, $keep) {
                $suffix = substr($keep->workOrder->wo_number, 3);
                $keep->update(['invoice_number' => 'INV-' . $suffix]);

                foreach ($invoices as $inv) {
                    if ($inv->id === $keep->id) {
                        continue;
                    }
                    FinancialTransaction::where('invoice_id', $inv->id)->update(['invoice_id' => $keep->id]);
                    $inv->items()->delete();
                    $inv->delete();
                }
            });

            $this->info("WO #{$woId} beres. Invoice yang dipertahankan: {$keep->fresh()->invoice_number}");
        }

        $this->newLine();
        $this->info('Selesai. Jalankan `php artisan migrate` untuk menerapkan unique constraint.');

        return self::SUCCESS;
    }
}
