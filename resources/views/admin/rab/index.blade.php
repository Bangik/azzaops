@extends('layouts.app')

@section('title', 'RAB')
@section('page-title', 'RAB')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Rencana Anggaran Biaya</h1>
        <p class="text-muted mb-0">Estimasi biaya instalasi sebelum pekerjaan dimulai.</p>
    </div>
    <a href="{{ route('admin.reports.index', ['type' => 'rab']) }}" class="btn btn-outline-primary">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i> Laporan RAB
    </a>
</div>

<div class="filter-bar">
    <form action="{{ route('admin.rab.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-5">
            <label class="form-label small">Cari RAB / Customer</label>
            <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                @foreach(App\Enums\RabStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-grid">
            <button class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Daftar RAB</h5>
        <span class="badge text-bg-light border">Total: {{ $rabs->total() }}</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. RAB</th>
                        <th>Judul</th>
                        <th>Customer</th>
                        <th class="text-end">Total</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rabs as $rab)
                    <tr>
                        <td><a href="{{ route('admin.rab.show', $rab) }}" class="fw-semibold text-decoration-none">{{ $rab->rab_number }}</a></td>
                        <td>{{ $rab->title }}</td>
                        <td>{{ $rab->customer->name }}</td>
                        <td class="text-end">Rp {{ number_format($rab->total, 0, ',', '.') }}</td>
                        <td><x-status-badge :status="$rab->status" /></td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.rab.show', $rab) }}" class="btn btn-sm btn-info text-white btn-action"><i class="bi bi-eye"></i></a>
                                <a href="{{ route('admin.rab.pdf', $rab) }}" class="btn btn-sm btn-danger btn-action"><i class="bi bi-file-pdf"></i></a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <i class="bi bi-calculator"></i>
                                <h6>Belum ada RAB</h6>
                                <p class="small text-muted">Buat RAB dari work order tipe instalasi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-footer">
            <div class="pagination-info">
                Menampilkan {{ $rabs->firstItem() ?? 0 }} - {{ $rabs->lastItem() ?? 0 }} dari {{ $rabs->total() }} data
            </div>
            {{ $rabs->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
