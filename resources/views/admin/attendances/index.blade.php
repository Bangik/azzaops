@extends('layouts.app')

@section('title', 'Log Presensi')
@section('page-title', 'Log Presensi Staff')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Log Presensi Staff</h1>
            <p class="text-muted mb-0">Rekap presensi seluruh staff berdasarkan rentang waktu.</p>
        </div>
        @if ($attendances->count() > 0)
            <div class="d-flex gap-2">
                <a href="{{ route('admin.attendances.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}"
                    class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
                </a>
                <a href="{{ route('admin.attendances.export', array_merge(request()->all(), ['format' => 'csv'])) }}"
                    class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor CSV
                </a>
            </div>
        @endif
    </div>

    {{-- Setting Jadwal Kerja --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Jadwal Kerja</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.attendances.update-schedule') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                @method('PUT')
                <div class="col-md-3">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="work_start_time" class="form-control"
                        value="{{ $schedule['work_start_time'] }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jam Pulang</label>
                    <input type="time" name="work_end_time" class="form-control"
                        value="{{ $schedule['work_end_time'] }}" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendances.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Staff</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua Staff</option>
                        @foreach ($staffList as $staff)
                            <option value="{{ $staff->id }}" {{ $userId == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staff->role->label() }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach (\App\Enums\AttendanceStatus::cases() as $s)
                            <option value="{{ $s->value }}" {{ $status == $s->value ? 'selected' : '' }}>
                                {{ $s->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-body">
            @if ($attendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="attendance-table">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Staff</th>
                                <th>Role</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Durasi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendances as $a)
                                <tr>
                                    <td>{{ $a->date->format('d/m/Y') }}</td>
                                    <td class="fw-semibold">{{ $a->user->name }}</td>
                                    <td>{{ $a->user->role->label() }}</td>
                                    <td>{{ $a->check_in ?? '-' }}</td>
                                    <td>{{ $a->check_out ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $a->status->color() }}">
                                            {{ $a->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $a->work_duration ?? '-' }}</td>
                                    <td>{{ $a->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $attendances->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    Tidak ada data presensi untuk filter yang dipilih.
                </div>
            @endif
        </div>
    </div>
@endsection
