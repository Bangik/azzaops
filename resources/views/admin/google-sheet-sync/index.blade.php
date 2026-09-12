@extends('layouts.app')
@section('title', 'Sync Google Spreadsheet')
@section('page-title', 'Sync Google Spreadsheet')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-table me-2"></i>Sync Data ke Google Spreadsheet</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">
                    Fitur ini akan mengirim seluruh data Work Order beserta relasi (customer, teknisi, invoice, RAB, laporan)
                    ke Google Spreadsheet untuk keperluan analisis manual, dashboard custom, atau kebutuhan lainnya.
                </p>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    <strong>Data yang disync:</strong> Semua Work Order dengan data customer, teknisi, item, invoice, RAB, dan laporan teknisi.
                    Data akan di-replace setiap kali sync (bukan append).
                </div>

                @if(empty(config('services.google.spreadsheet_id')))
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Belum dikonfigurasi!</strong> Set <code>GOOGLE_SPREADSHEET_ID</code> di file <code>.env</code> terlebih dahulu.
                    </div>
                @else
                    <form action="{{ route('admin.google-sheet-sync.sync') }}" method="POST"
                        onsubmit="return confirm('Yakin ingin sync data? Data di spreadsheet akan di-replace.')">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-5">
                                <label for="start_date" class="form-label">Tanggal Mulai (opsional)</label>
                                <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}">
                                <div class="form-text">Berdasarkan tanggal pembuatan Work Order</div>
                            </div>
                            <div class="col-md-5">
                                <label for="end_date" class="form-label">Tanggal Akhir (opsional)</label>
                                <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date') }}">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success" id="btnSync">
                            <i class="bi bi-arrow-repeat me-1"></i>Sync Sekarang
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-terminal me-2"></i>Sync via Terminal (Artisan)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Bisa juga menjalankan sync via terminal atau cron job:</p>
                <pre class="bg-dark text-light p-3 rounded"><code>php artisan sync:work-orders-sheet --start="2026-01-01" --end="2026-01-31"</code></pre>

                <p class="text-muted mt-3">Untuk auto-sync setiap hari khusus data bulan ini saja, tambahkan di cron:</p>
                <pre class="bg-dark text-light p-3 rounded"><code>0 6 * * * cd /path/to/azzaops && php artisan sync:work-orders-sheet --start="$(date +\%Y-\%m-01)" >> /dev/null 2>&1</code></pre>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-columns me-2"></i>Kolom yang Disync</h5>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush small">
                    @foreach([
                        'No WO', 'Tanggal Dibuat', 'Tanggal Jadwal', 'Jam Jadwal', 'Urutan Job',
                        'Tipe', 'Kategori Jasa', 'Status', 'Customer', 'Tipe Customer',
                        'Company', 'Phone Customer', 'Lokasi', 'Judul', 'Deskripsi',
                        'Vendor', 'Teknisi', 'Status Assignment', 'Estimasi Biaya', 'Total Biaya',
                        'Total Item', 'Total Vendor', 'Item Detail', 'Temuan Teknisi',
                        'Pekerjaan Dilakukan', 'Rekomendasi', 'Material Dipakai',
                        'No Invoice', 'Status Invoice', 'Status Bayar', 'Total Invoice',
                        'Metode Bayar', 'Tgl Bayar', 'No RAB', 'Status RAB', 'Total RAB',
                        'Durasi (menit)', 'Catatan', 'Dibuat Oleh', 'Parent WO',
                        'Link GMaps', 'Mulai Dikerjakan', 'Selesai Dikerjakan',
                    ] as $i => $col)
                        <div class="list-group-item py-1 px-3">{{ $i + 1 }}. {{ $col }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
