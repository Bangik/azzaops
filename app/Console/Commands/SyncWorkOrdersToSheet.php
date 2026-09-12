<?php

namespace App\Console\Commands;

use App\Models\WorkOrder;
use App\Services\GoogleSheetService;
use Illuminate\Console\Command;

class SyncWorkOrdersToSheet extends Command
{
    protected $signature = 'sync:work-orders-sheet {--start= : Tanggal mulai (Y-m-d)} {--end= : Tanggal akhir (Y-m-d)}';
    protected $description = 'Sync data Work Order (joined) ke Google Spreadsheet berdasarkan rentang tanggal';

    public function handle(GoogleSheetService $sheetService): int
    {
        $spreadsheetId = config('services.google.spreadsheet_id');

        if (empty($spreadsheetId)) {
            $this->error('GOOGLE_SPREADSHEET_ID belum diset di .env');
            return self::FAILURE;
        }

        $startDate = $this->option('start');
        $endDate = $this->option('end');

        $sheetName = 'Work Orders';
        if ($startDate && $endDate) {
            $this->info("Mengambil data work orders dari $startDate sampai $endDate...");
        } else {
            $this->info('Mengambil seluruh data work orders...');
        }

        // ============================================================
        // CUSTOM COLUMNS — tambah/hapus kolom di sini
        // ============================================================
        $headers = [
            'No WO',
            'Tanggal Dibuat',
            'Tanggal Jadwal',
            'Jam Jadwal',
            'Urutan Job',
            'Tipe',
            'Kategori Jasa',
            'Status',
            'Customer',
            'Tipe Customer',
            'Company',
            'Phone Customer',
            'Lokasi',
            'Judul',
            'Deskripsi',
            'Vendor',
            'Teknisi',
            'Status Assignment',
            'Estimasi Biaya',
            'Total Biaya',
            'Total Item',
            'Total Vendor',
            'Item Detail',
            'Temuan Teknisi',
            'Pekerjaan Dilakukan',
            'Rekomendasi',
            'Material Dipakai',
            'No Invoice',
            'Status Invoice',
            'Status Bayar',
            'Total Invoice',
            'Metode Bayar',
            'Tgl Bayar',
            'No RAB',
            'Status RAB',
            'Total RAB',
            'Durasi (menit)',
            'Catatan',
            'Dibuat Oleh',
            'Parent WO',
            'Link GMaps',
            'Mulai Dikerjakan',
            'Selesai Dikerjakan',
        ];

        // ============================================================
        // QUERY — eager load semua relasi yang dibutuhkan
        // ============================================================
        $query = WorkOrder::with([
            'type',
            'customer',
            'vendor',
            'serviceCategory',
            'creator',
            'items',
            'assignments.technician',
            'reports',
            'invoice',
            'rab',
        ]);

        if ($startDate) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        $workOrders = $query->orderBy('created_at', 'desc')->get();

        // ============================================================
        // MAP DATA — sesuaikan dengan headers di atas
        // Untuk tambah kolom: tambah header + tambah entry di array bawah
        // ============================================================
        $rows = $workOrders->map(function (WorkOrder $wo) {
            // Gabung nama teknisi
            $technicians = $wo->assignments
                ->map(fn($a) => $a->technician?->name)
                ->filter()
                ->implode(', ');

            $assignmentStatuses = $wo->assignments
                ->map(fn($a) => ($a->technician?->name ?? '?') . ': ' . ($a->status->value ?? ''))
                ->implode(', ');

            // Gabung item detail
            $itemDetail = $wo->items
                ->map(fn($i) => "{$i->description} ({$i->quantity} x " . number_format($i->unit_price, 0, ',', '.') . ")")
                ->implode(' | ');

            // Ambil report terakhir
            $latestReport = $wo->reports->sortByDesc('submitted_at')->first();

            // Invoice
            $inv = $wo->invoice;

            // RAB
            $rab = $wo->rab;

            // Gunakan string kosong '' alih-alih null untuk menghindari Google\Model stripping array keys
            return [
                $wo->wo_number ?? '',
                $wo->created_at ? $wo->created_at->format('Y-m-d H:i') : '',
                $wo->scheduled_date ? $wo->scheduled_date->format('Y-m-d') : '',
                $wo->scheduled_time ?? '',
                $wo->job_order ?? '',
                $wo->type?->name ?? '',
                $wo->serviceCategory?->name ?? '',
                $wo->status?->value ?? '',
                $wo->customer?->name ?? '',
                $wo->customer?->type instanceof \BackedEnum ? $wo->customer->type->value : ($wo->customer?->type ?? ''),
                $wo->customer?->company_name ?? '',
                $wo->customer?->phone ?? '',
                $wo->location ?? '',
                $wo->title ?? '',
                strip_tags($wo->description ?? ''),
                $wo->vendor?->name ?? '',
                $technicians ?? '',
                $assignmentStatuses ?? '',
                $wo->estimated_cost ? (float) $wo->estimated_cost : '',
                $wo->total_cost ? (float) $wo->total_cost : '',
                (float) $wo->total, // accessor: sum items
                (float) $wo->vendor_total, // accessor: sum vendor items
                $itemDetail ?? '',
                strip_tags($latestReport?->findings ?? ''),
                strip_tags($latestReport?->work_done ?? ''),
                strip_tags($latestReport?->recommendations ?? ''),
                strip_tags($latestReport?->materials_used ?? ''),
                $inv?->invoice_number ?? '',
                $inv?->status instanceof \BackedEnum ? $inv->status->value : ($inv?->status ?? ''),
                $inv?->payment_status instanceof \BackedEnum ? $inv->payment_status->value : ($inv?->payment_status ?? ''),
                $inv ? (float) $inv->total : '',
                $inv?->payment_method ?? '',
                $inv?->payment_date ? $inv->payment_date->format('Y-m-d') : '',
                $rab?->rab_number ?? '',
                $rab?->status instanceof \BackedEnum ? $rab->status->value : ($rab?->status ?? ''),
                $rab ? (float) $rab->total : '',
                $wo->duration_minutes ?? '',
                strip_tags($wo->notes ?? ''),
                $wo->creator?->name ?? '',
                $wo->parentWorkOrder?->wo_number ?? '',
                $wo->gmaps_link ?? '',
                $wo->started_at ? $wo->started_at->format('Y-m-d H:i') : '',
                $wo->completed_at ? $wo->completed_at->format('Y-m-d H:i') : '',
            ];
        })->toArray();

        $this->info("Total: {$workOrders->count()} work orders");
        $this->info('Membuat sheet jika belum ada...');

        $sheetService->ensureSheetExists($spreadsheetId, $sheetName);

        $this->info('Mengirim data ke Google Spreadsheet...');

        $count = $sheetService->syncSheet($spreadsheetId, $sheetName, $headers, $rows);

        $this->info("Selesai! {$count} baris data berhasil disync.");

        return self::SUCCESS;
    }
}
