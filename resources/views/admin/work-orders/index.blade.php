@extends('layouts.app')

@section('title', 'Work Order')
@section('page-title', 'Work Order')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Work Order</h1>
        <p class="text-muted mb-0">Kelola perintah kerja operasional lapangan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.index', ['type' => 'wo']) }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Laporan WO
        </a>
        <a href="{{ route('admin.work-orders.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Buat Work Order
        </a>
    </div>
</div>

{{-- Filter Bar --}}
<div class="filter-bar">
    <form action="{{ route('admin.work-orders.index') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-4">
            <label for="q" class="form-label small">Cari Pekerjaan / Customer</label>
            <input type="text" class="form-control form-control-sm" id="q" name="q" value="{{ request('q') }}" placeholder="No. WO, nama customer...">
        </div>
        <div class="col-md-3">
            <label for="status" class="form-label small">Status</label>
            <select class="form-select form-select-sm" id="status" name="status">
                <option value="">Semua Status</option>
                @foreach(App\Enums\WorkOrderStatus::cases() as $status)
                    <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label for="type" class="form-label small">Tipe</label>
            <select class="form-select form-select-sm" id="type" name="type">
                <option value="">Semua Tipe</option>
                @foreach(App\Enums\WorkOrderType::cases() as $type)
                    <option value="{{ $type->value }}" {{ request('type') == $type->value ? 'selected' : '' }}>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-grid">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel me-1"></i> Filter
            </button>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Daftar Perintah Kerja</h5>
            <p class="text-muted small mb-0">Total ditemukan: {{ $workOrders->total() }}</p>
        </div>
        <span class="badge text-bg-light border">Halaman {{ $workOrders->currentPage() }}</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. WO</th>
                        <th>Tipe</th>
                        <th>Customer</th>
                        <th>Kategori</th>
                        <th>Tanggal Rencana</th>
                        <th>Teknisi</th>
                        <th>Prioritas</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($workOrders as $wo)
                    <tr>
                        <td>
                            <a href="{{ route('admin.work-orders.show', $wo) }}" class="text-decoration-none fw-semibold">
                                {{ $wo->wo_number }}
                            </a>
                        </td>
                        <td>{{ $wo->type->label() }}</td>
                        <td>
                            <div class="fw-semibold">{{ $wo->customer->name }}</div>
                            @if($wo->customer->company_name)
                                <div class="text-muted small">{{ $wo->customer->company_name }}</div>
                            @endif
                        </td>
                        <td>{{ $wo->serviceCategory->name }}</td>
                        <td>{{ $wo->scheduled_date ? $wo->scheduled_date->format('d/m/Y') : '-' }}</td>
                        <td>
                            @forelse($wo->assignments as $assign)
                                <span class="badge bg-secondary mb-1 d-inline-block">{{ $assign->technician->name }}</span>
                            @empty
                                <span class="text-muted small">Belum ditugaskan</span>
                            @endforelse
                        </td>
                        <td>
                            <span class="badge bg-{{ $wo->priority->color() }}">{{ $wo->priority->label() }}</span>
                        </td>
                        <td>
                            <x-status-badge :status="$wo->status" />
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.work-orders.show', $wo) }}" class="btn btn-sm btn-info text-white btn-action" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if($wo->status !== App\Enums\WorkOrderStatus::Completed)
                                <a href="{{ route('admin.work-orders.edit', $wo) }}" class="btn btn-sm btn-warning btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.work-orders.destroy', $wo) }}" method="POST" onsubmit="return confirmDelete('Yakin ingin membatalkan work order ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" title="Batalkan">
                                        <i class="bi bi-x-circle"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9">
                            <div class="empty-state">
                                <i class="bi bi-clipboard-x"></i>
                                <h6>Tidak ada data Work Order ditemukan</h6>
                                <p class="small text-muted">Coba ubah filter atau kata kunci pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-footer">
            <div class="pagination-info">
                Menampilkan {{ $workOrders->firstItem() ?? 0 }} - {{ $workOrders->lastItem() ?? 0 }} dari {{ $workOrders->total() }} data
            </div>
            {{ $workOrders->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
