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
                <input type="text" class="form-control form-control-sm" id="q" name="q"
                    value="{{ request('q') }}" placeholder="No. WO, nama customer...">
            </div>
            <div class="col-md-3">
                <label for="status" class="form-label small">Status</label>
                <select class="form-select form-select-sm" id="status" name="status">
                    <option value="">Semua Status</option>
                    @foreach (App\Enums\WorkOrderStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>
                            {{ $status->label() }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="work_order_type_id" class="form-label">Tipe</label>
                <select class="form-select form-select-sm" id="work_order_type_id" name="work_order_type_id">
                    <option value="">Semua Tipe</option>
                    @foreach ($types as $type)
                        <option value="{{ $type->id }}"
                            {{ request('work_order_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
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
                            <th>Tanggal/Jam Rencana</th>
                            <th>Urutan</th>
                            <th>Teknisi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($workOrders as $wo)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.work-orders.show', $wo) }}"
                                        class="text-decoration-none fw-semibold">
                                        {{ $wo->wo_number }}
                                    </a>
                                </td>
                                <td>{{ $wo->type->name }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $wo->customer->name }}</div>
                                    @if ($wo->customer->company_name)
                                        <div class="text-muted small">{{ $wo->customer->company_name }}</div>
                                    @endif
                                </td>
                                <td>{{ $wo->serviceCategory->name }}</td>
                                <td>
                                    {{ $wo->scheduled_date ? $wo->scheduled_date->format('d/m/Y') : '-' }}
                                    @if ($wo->scheduled_time)
                                        <br><span class="text-muted small"><i class="bi bi-clock"></i>
                                            {{ date('H:i', strtotime($wo->scheduled_time)) }}</span>
                                    @endif
                                </td>
                                <td>{{ $wo->job_order ?? '-' }}</td>
                                <td>
                                    @forelse($wo->assignments as $assign)
                                        <span
                                            class="badge bg-secondary mb-1 d-inline-block">{{ $assign->technician->name }}</span>
                                    @empty
                                        <span class="text-muted small">Belum ditugaskan</span>
                                    @endforelse
                                </td>
                                <td>
                                    <x-status-badge :status="$wo->status" />
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.work-orders.show', $wo) }}"
                                            class="btn btn-sm btn-info text-white btn-action" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        @if (!$wo->invoice)
                                            <a href="{{ route('admin.invoices.create', ['work_order_id' => $wo->id]) }}"
                                                class="btn btn-sm btn-primary btn-action" title="Generate Invoice">
                                                <i class="bi bi-receipt"></i>
                                            </a>
                                        @endif
                                        @if ($wo->invoice)
                                            <a href="{{ route('admin.invoices.pdf', $wo->invoice) }}"
                                                class="btn btn-sm btn-danger btn-action" title="Download Invoice">
                                                <i class="bi bi-file-pdf"></i>
                                            </a>
                                            @if ($wo->invoice->payment_status === App\Enums\PaymentStatus::Paid)
                                                <a href="{{ route('admin.invoices.receipt', $wo->invoice) }}"
                                                    class="btn btn-sm btn-success btn-action" title="Download Kwitansi">
                                                    <i class="bi bi-file-earmark-check"></i>
                                                </a>
                                            @endif
                                        @endif
                                        @if ($wo->status !== App\Enums\WorkOrderStatus::Completed)
                                            <a href="{{ route('admin.work-orders.edit', $wo) }}"
                                                class="btn btn-sm btn-warning btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            @if (auth()->user()->role->value !== 'admin')
                                                <form action="{{ route('admin.work-orders.destroy', $wo) }}" method="POST"
                                                    onsubmit="return confirmDelete('Yakin ingin membatalkan work order ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger btn-action"
                                                        title="Batalkan">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endif
                                        @if (auth()->user()->role->value === 'super_admin')
                                            <form action="{{ route('admin.work-orders.destroy', $wo) }}" method="POST"
                                                onsubmit="return confirmDelete('Yakin ingin MENGHAPUS work order ini secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger btn-action"
                                                    title="Hapus Permanen">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10">
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
                    Menampilkan {{ $workOrders->firstItem() ?? 0 }} - {{ $workOrders->lastItem() ?? 0 }} dari
                    {{ $workOrders->total() }} data
                </div>
                {{ $workOrders->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
@endsection
