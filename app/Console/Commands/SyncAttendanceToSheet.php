<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Services\GoogleSheetService;
use Illuminate\Console\Command;

class SyncAttendanceToSheet extends Command
{
    protected $signature = 'sync:attendance-sheet {--start= : Tanggal mulai (Y-m-d)} {--end= : Tanggal akhir (Y-m-d)}';
    protected $description = 'Sync data Presensi (joined) ke Google Spreadsheet berdasarkan rentang tanggal';

    public function handle(GoogleSheetService $sheetService): int
    {
        $spreadsheetId = config('services.google.spreadsheet_id');

        if (empty($spreadsheetId)) {
            $this->error('GOOGLE_SPREADSHEET_ID belum diset di .env');
            return self::FAILURE;
        }

        $startDate = $this->option('start');
        $endDate = $this->option('end');

        $sheetName = 'Presensi';
        if ($startDate && $endDate) {
            $this->info("Mengambil data presensi dari $startDate sampai $endDate...");
        } else {
            $this->info('Mengambil seluruh data presensi...');
        }

        // ============================================================
        // CUSTOM COLUMNS
        // ============================================================
        $headers = [
            'ID',
            'Nama Staff',
            'Role',
            'Email',
            'Phone',
            'Tanggal',
            'Jam Masuk',
            'Jam Pulang',
            'Durasi Kerja',
            'Status',
            'Catatan',
            'Latitude Masuk',
            'Longitude Masuk',
            'Latitude Pulang',
            'Longitude Pulang',
            'Tgl Buat Data',
        ];

        // ============================================================
        // QUERY — eager load semua relasi yang dibutuhkan
        // ============================================================
        $query = Attendance::with(['user']);

        if ($startDate) {
            $query->whereDate('date', '>=', $startDate);
        }
        if ($endDate) {
            $query->whereDate('date', '<=', $endDate);
        }

        $attendances = $query->orderBy('date', 'desc')
                             ->orderBy('created_at', 'desc')
                             ->get();

        // ============================================================
        // MAP DATA
        // ============================================================
        $rows = $attendances->map(function (Attendance $att) {
            return [
                $att->id,
                $att->user?->name ?? '',
                $att->user?->role instanceof \BackedEnum ? $att->user->role->value : ($att->user?->role ?? ''),
                $att->user?->email ?? '',
                $att->user?->phone ?? '',
                $att->date ? $att->date->format('Y-m-d') : '',
                $att->check_in ?? '',
                $att->check_out ?? '',
                $att->work_duration ?? '',
                $att->status instanceof \BackedEnum ? $att->status->value : ($att->status ?? ''),
                strip_tags($att->notes ?? ''),
                $att->check_in_latitude ?? '',
                $att->check_in_longitude ?? '',
                $att->check_out_latitude ?? '',
                $att->check_out_longitude ?? '',
                $att->created_at ? $att->created_at->format('Y-m-d H:i') : '',
            ];
        })->toArray();

        $this->info("Total: {$attendances->count()} data presensi");
        $this->info('Membuat sheet jika belum ada...');

        $sheetService->ensureSheetExists($spreadsheetId, $sheetName);

        $this->info('Mengirim data ke Google Spreadsheet...');

        $count = $sheetService->syncSheet($spreadsheetId, $sheetName, $headers, $rows);

        $this->info("Selesai! {$count} baris data presensi berhasil disync.");

        return self::SUCCESS;
    }
}


