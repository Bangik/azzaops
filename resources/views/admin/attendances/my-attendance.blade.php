@extends('layouts.app')

@section('title', 'Presensi Saya')
@section('page-title', 'Presensi Saya')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Presensi Saya</h1>
            <p class="text-muted mb-0">Lakukan presensi masuk dan pulang harian.</p>
        </div>
    </div>

    <div class="row">
        {{-- Kartu Presensi Hari Ini --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Hari Ini — {{ now()->translatedFormat('l, d F Y') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Jadwal Kerja</small>
                        <div class="fw-semibold">{{ $schedule['work_start_time'] }} - {{ $schedule['work_end_time'] }}</div>
                    </div>

                    @if ($today)
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Jam Masuk</small>
                                <div class="fw-semibold fs-5 text-success">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    {{ $today->check_in ?? '-' }}
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Jam Pulang</small>
                                <div class="fw-semibold fs-5 text-danger">
                                    <i class="bi bi-box-arrow-right me-1"></i>
                                    {{ $today->check_out ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Status</small>
                            <div>
                                <span class="badge bg-{{ $today->status->color() }} fs-6">
                                    {{ $today->status->label() }}
                                </span>
                            </div>
                        </div>
                        @if ($today->work_duration)
                            <div class="mb-3">
                                <small class="text-muted">Durasi Kerja</small>
                                <div class="fw-semibold">{{ $today->work_duration }}</div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                            Belum ada presensi hari ini.
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="d-grid gap-2 mt-3">
                        @if (!$today || !$today->check_in)
                            <form action="{{ route('admin.attendances.check-in') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="notes" class="form-control form-control-sm"
                                        placeholder="Catatan (opsional)">
                                </div>
                                <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Konfirmasi presensi masuk?')">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Presensi Masuk
                                </button>
                            </form>
                        @elseif (!$today->check_out)
                            <form action="{{ route('admin.attendances.check-out') }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <input type="text" name="notes" class="form-control form-control-sm"
                                        placeholder="Catatan (opsional)">
                                </div>
                                <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Konfirmasi presensi pulang?')">
                                    <i class="bi bi-box-arrow-right me-1"></i> Presensi Pulang
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info mb-0 text-center">
                                <i class="bi bi-check-circle me-1"></i> Presensi hari ini sudah lengkap.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Presensi --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Presensi Terakhir</h6>
                </div>
                <div class="card-body p-0">
                    @if ($logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Masuk</th>
                                        <th>Pulang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logs as $log)
                                        <tr>
                                            <td>{{ $log->date->format('d/m/Y') }}</td>
                                            <td>{{ $log->check_in ?? '-' }}</td>
                                            <td>{{ $log->check_out ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $log->status->color() }}">
                                                    {{ $log->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $logs->links() }}
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            Belum ada riwayat presensi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
