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
                    Fitur ini akan mengirim seluruh data (Work Order atau Keuangan) beserta relasinya
                    ke Google Spreadsheet untuk keperluan analisis manual dan pembuatan dashboard custom.
                </p>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-1"></i>
                    Data akan di-replace pada sheet/tab yang bersangkutan setiap kali sync (bukan append).
                </div>

                @if(empty(config('services.google.spreadsheet_id')))
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        <strong>Belum dikonfigurasi!</strong> Set <code>GOOGLE_SPREADSHEET_ID</code> di file <code>.env</code> terlebih dahulu.
                    </div>
                @else
                    <ul class="nav nav-tabs mb-3" id="syncTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="wo-tab" data-bs-toggle="tab" data-bs-target="#wo-pane" type="button" role="tab">Work Order</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="finance-tab" data-bs-toggle="tab" data-bs-target="#finance-pane" type="button" role="tab">Keuangan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="attendance-tab" data-bs-toggle="tab" data-bs-target="#attendance-pane" type="button" role="tab">Presensi</button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="syncTabsContent">
                        <!-- Work Order Form -->
                        <div class="tab-pane fade show active" id="wo-pane" role="tabpanel" tabindex="0">
                            <form action="{{ route('admin.google-sheet-sync.sync') }}" method="POST"
                                onsubmit="return confirm('Yakin ingin sync data Work Order? Data di tab Work Orders akan di-replace.')">
                                @csrf
                                <input type="hidden" name="type" value="work-orders">
                                
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label for="wo_start_date" class="form-label">Tanggal Mulai (opsional)</label>
                                        <input type="date" class="form-control" id="wo_start_date" name="start_date" value="{{ old('start_date') }}">
                                        <div class="form-text">Berdasarkan tanggal pembuatan WO</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="wo_end_date" class="form-label">Tanggal Akhir (opsional)</label>
                                        <input type="date" class="form-control" id="wo_end_date" name="end_date" value="{{ old('end_date') }}">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-arrow-repeat me-1"></i>Sync Work Order
                                </button>
                            </form>
                        </div>

                        <!-- Finance Form -->
                        <div class="tab-pane fade" id="finance-pane" role="tabpanel" tabindex="0">
                            <form action="{{ route('admin.google-sheet-sync.sync') }}" method="POST"
                                onsubmit="return confirm('Yakin ingin sync data Keuangan? Data di tab Keuangan akan di-replace.')">
                                @csrf
                                <input type="hidden" name="type" value="finance">
                                
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label for="fin_start_date" class="form-label">Tanggal Mulai (opsional)</label>
                                        <input type="date" class="form-control" id="fin_start_date" name="start_date" value="{{ old('start_date') }}">
                                        <div class="form-text">Berdasarkan tanggal transaksi</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="fin_end_date" class="form-label">Tanggal Akhir (opsional)</label>
                                        <input type="date" class="form-control" id="fin_end_date" name="end_date" value="{{ old('end_date') }}">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-arrow-repeat me-1"></i>Sync Keuangan
                                </button>
                            </form>
                        </div>
                        
                        <!-- Attendance Form -->
                        <div class="tab-pane fade" id="attendance-pane" role="tabpanel" tabindex="0">
                            <form action="{{ route('admin.google-sheet-sync.sync') }}" method="POST"
                                onsubmit="return confirm('Yakin ingin sync data Presensi? Data di tab Presensi akan di-replace.')">
                                @csrf
                                <input type="hidden" name="type" value="attendance">
                                
                                <div class="row mb-3">
                                    <div class="col-md-5">
                                        <label for="att_start_date" class="form-label">Tanggal Mulai (opsional)</label>
                                        <input type="date" class="form-control" id="att_start_date" name="start_date" value="{{ old('start_date') }}">
                                        <div class="form-text">Berdasarkan tanggal presensi</div>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="att_end_date" class="form-label">Tanggal Akhir (opsional)</label>
                                        <input type="date" class="form-control" id="att_end_date" name="end_date" value="{{ old('end_date') }}">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-info text-white">
                                    <i class="bi bi-arrow-repeat me-1"></i>Sync Presensi
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-terminal me-2"></i>Sync via Terminal (Artisan)</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Bisa juga menjalankan sync via terminal atau cron job:</p>
                <pre class="bg-dark text-light p-3 rounded"><code>php artisan sync:work-orders-sheet --start="2026-01-01" --end="2026-01-31"
php artisan sync:finance-sheet --start="2026-01-01" --end="2026-01-31"
php artisan sync:attendance-sheet --start="2026-01-01" --end="2026-01-31"</code></pre>

                <p class="text-muted mt-3">Untuk auto-sync setiap hari khusus data bulan ini saja, tambahkan di cron:</p>
                <pre class="bg-dark text-light p-3 rounded"><code>0 6 * * * cd /path/to/azzaops && php artisan sync:work-orders-sheet --start="$(date +\%Y-\%m-01)" >> /dev/null 2>&1
0 6 * * * cd /path/to/azzaops && php artisan sync:finance-sheet --start="$(date +\%Y-\%m-01)" >> /dev/null 2>&1
0 6 * * * cd /path/to/azzaops && php artisan sync:attendance-sheet --start="$(date +\%Y-\%m-01)" >> /dev/null 2>&1</code></pre>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-columns me-2"></i>Kolom Work Order</h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
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
        
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-columns me-2"></i>Kolom Keuangan</h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <div class="list-group list-group-flush small">
                    @foreach([
                        'ID Transaksi', 'Tanggal', 'Tipe', 'Kategori', 'Akun Keuangan',
                        'Nominal', 'Deskripsi', 'No Referensi', 'Pencatat',
                        'No Invoice', 'Status Invoice', 'Customer',
                        'No WO', 'Judul WO', 'PIC Pengeluaran', 'Tgl Buat Data',
                    ] as $i => $col)
                        <div class="list-group-item py-1 px-3">{{ $i + 1 }}. {{ $col }}</div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="bi bi-list-columns me-2"></i>Kolom Presensi</h5>
            </div>
            <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                <div class="list-group list-group-flush small">
                    @foreach([
                        'ID', 'Nama Staff', 'Role', 'Email', 'Phone',
                        'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Durasi Kerja', 'Status', 'Catatan',
                        'Latitude Masuk', 'Longitude Masuk', 'Latitude Pulang', 'Longitude Pulang',
                        'Tgl Buat Data',
                    ] as $i => $col)
                        <div class="list-group-item py-1 px-3">{{ $i + 1 }}. {{ $col }}</div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
