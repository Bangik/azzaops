@extends('layouts.app')

@section('title', 'Laporan Operasional')
@section('page-title', 'Laporan Operasional')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Rangkuman Laporan & Analitik</h1>
            <p class="text-muted mb-0">Analisis data operasional dan unduh laporan ke format Excel / CSV.</p>
        </div>
        @if ($data->count() > 0)
            <div class="d-flex gap-2">
                <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}"
                    class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel (.xlsx)
                </a>
                <a href="{{ route('admin.reports.export', array_merge(request()->all(), ['format' => 'csv'])) }}"
                    class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor CSV
                </a>
            </div>
        @endif
    </div>

    {{-- Tabs for different reports --}}
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link {{ $type === 'wo' ? 'active' : '' }}"
                href="{{ route('admin.reports.index', ['type' => 'wo', 'from' => $from, 'to' => $to]) }}">
                <i class="bi bi-clipboard-check me-1"></i> Work Order
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'customers' ? 'active' : '' }}"
                href="{{ route('admin.reports.index', ['type' => 'customers', 'from' => $from, 'to' => $to]) }}">
                <i class="bi bi-people me-1"></i> Customer
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'invoices' ? 'active' : '' }}"
                href="{{ route('admin.reports.index', ['type' => 'invoices', 'from' => $from, 'to' => $to]) }}">
                <i class="bi bi-receipt me-1"></i> Invoice
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'rab' ? 'active' : '' }}"
                href="{{ route('admin.reports.index', ['type' => 'rab', 'from' => $from, 'to' => $to]) }}">
                <i class="bi bi-calculator me-1"></i> RAB
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'finance' ? 'active' : '' }}"
                href="{{ route('admin.reports.index', ['type' => 'finance', 'from' => $from, 'to' => $to]) }}">
                <i class="bi bi-cash-stack me-1"></i> Keuangan
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $type === 'staff' ? 'active' : '' }}"
                href="{{ route('admin.reports.index', ['type' => 'staff', 'from' => $from, 'to' => $to]) }}">
                <i class="bi bi-person-badge me-1"></i> Staff
            </a>
        </li>
    </ul>

    {{-- Filter Bar --}}
    <div class="filter-bar">
        <form action="{{ route('admin.reports.index') }}" method="GET" class="row g-2 align-items-end">
            <input type="hidden" name="type" value="{{ $type }}">

            <div class="col-md-3">
                <label class="form-label small">Dari Tanggal</label>
                <input type="text" name="from" class="form-control form-control-sm datepicker"
                    value="{{ $from }}" required>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="text" name="to" class="form-control form-control-sm datepicker"
                    value="{{ $to }}" required>
            </div>

            @if ($type === 'wo')
                <div class="col-md-2">
                    <label class="form-label small">Staff / Teknisi</label>
                    <select name="technician_id" class="form-select form-select-sm">
                        <option value="">Semua Teknisi</option>
                        @foreach ($technicians as $tech)
                            <option value="{{ $tech->id }}" @selected($techId == $tech->id)>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">Status WO</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach (App\Enums\WorkOrderStatus::cases() as $s)
                            <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($type === 'invoices')
                <div class="col-md-4">
                    <label class="form-label small">Status Invoice</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach (App\Enums\InvoiceStatus::cases() as $s)
                            <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($type === 'rab')
                <div class="col-md-4">
                    <label class="form-label small">Status RAB</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Status</option>
                        @foreach (App\Enums\RabStatus::cases() as $s)
                            <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if ($type === 'finance')
                <div class="col-md-4">
                    <label class="form-label small">Tipe Transaksi</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">Semua Tipe</option>
                        @foreach (App\Enums\TransactionType::cases() as $s)
                            <option value="{{ $s->value }}" @selected($status === $s->value)>{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if (in_array($type, ['customers', 'staff']))
                <div class="col-md-4">
                    {{-- Spacing helper --}}
                </div>
            @endif

            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Filter</button>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    @switch($type)
                        @case('customers')
                            Data Laporan Pelanggan
                        @break

                        @case('invoices')
                            Data Laporan Invoice
                        @break

                        @case('rab')
                            Data Laporan RAB
                        @break

                        @case('finance')
                            Data Transaksi Keuangan
                        @break

                        @case('staff')
                            Data Kinerja Staff
                        @break

                        @default
                            Data Laporan Work Order
                    @endswitch
                </h5>
                <p class="text-muted small mb-0">Menampilkan hasil berdasarkan filter tanggal dan atribut.</p>
            </div>
            <span class="badge text-bg-light border">Total: {{ $data->total() }} Data</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                @if ($type === 'customers')
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Customer</th>
                                <th>Tipe</th>
                                <th>Perusahaan</th>
                                <th>Telepon</th>
                                <th>Email</th>
                                <th>Kota</th>
                                <th>Market</th>
                                <th class="text-end">Jumlah WO</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $c)
                                <tr>
                                    <td>{{ $c->name }}</td>
                                    <td>{{ $c->type->label() }}</td>
                                    <td>{{ $c->company_name ?? '-' }}</td>
                                    <td>{{ $c->phone }}</td>
                                    <td>{{ $c->email ?? '-' }}</td>
                                    <td>{{ $c->city ?? '-' }}</td>
                                    <td>{{ $c->market ?? '-' }}</td>
                                    <td class="text-end fw-semibold">{{ $c->work_orders_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Tidak ada data customer
                                        ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($type === 'invoices')
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. Invoice</th>
                                <th>Customer</th>
                                <th>Subtotal</th>
                                <th>Diskon</th>
                                <th>Pajak</th>
                                <th class="text-end">Grand Total</th>
                                <th>Status Bayar</th>
                                <th>Status Tagihan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $inv)
                                <tr>
                                    <td><a href="{{ route('admin.invoices.show', $inv) }}"
                                            class="fw-semibold text-decoration-none">{{ $inv->invoice_number }}</a></td>
                                    <td>{{ $inv->customer->name }}</td>
                                    <td>Rp {{ number_format($inv->subtotal, 0, ',', '.') }}</td>
                                    <td>-Rp {{ number_format($inv->discount, 0, ',', '.') }}</td>
                                    <td>Rp {{ number_format($inv->tax_amount, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-primary">Rp
                                        {{ number_format($inv->total, 0, ',', '.') }}</td>
                                    <td><x-status-badge :status="$inv->payment_status" /></td>
                                    <td><x-status-badge :status="$inv->status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">Tidak ada data invoice
                                        ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($type === 'rab')
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. RAB</th>
                                <th>Judul Proyek</th>
                                <th>Customer</th>
                                <th>Subtotal</th>
                                <th class="text-end">Total</th>
                                <th>Masa Berlaku</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $r)
                                <tr>
                                    <td><a href="{{ route('admin.rab.show', $r) }}"
                                            class="fw-semibold text-decoration-none">{{ $r->rab_number }}</a></td>
                                    <td>{{ $r->title }}</td>
                                    <td>{{ $r->customer->name }}</td>
                                    <td>Rp {{ number_format($r->subtotal, 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold">Rp {{ number_format($r->total, 0, ',', '.') }}</td>
                                    <td>{{ $r->valid_until ? $r->valid_until->format('d/m/Y') : '-' }}</td>
                                    <td><x-status-badge :status="$r->status" /></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Tidak ada data RAB ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($type === 'finance')
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Tipe</th>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $t)
                                <tr>
                                    <td>{{ $t->transaction_date->format('d/m/Y') }}</td>
                                    <td><span
                                            class="badge {{ $t->type->value === 'income' ? 'bg-success' : 'bg-danger' }}">{{ $t->type->label() }}</span>
                                    </td>
                                    <td>{{ $t->category ? $t->category->name : ($t->type->value === 'income' ? 'Pembayaran Jasa' : 'Lainnya') }}
                                    </td>
                                    <td>{{ $t->description }}</td>
                                    <td
                                        class="text-end fw-semibold {{ $t->type->value === 'income' ? 'text-success' : 'text-danger' }}">
                                        {{ $t->type->value === 'income' ? '+' : '-' }}Rp
                                        {{ number_format($t->amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">Tidak ada data transaksi
                                        keuangan ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @elseif($type === 'staff')
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Staff</th>
                                <th>Email</th>
                                <th>Telepon</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-center">Jumlah Tugas (WO)</th>
                                <th class="text-center">Jumlah Laporan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $s)
                                <tr>
                                    <td>{{ $s->name }}</td>
                                    <td>{{ $s->email }}</td>
                                    <td>{{ $s->phone ?? '-' }}</td>
                                    <td><span class="badge text-bg-light border">{{ $s->role->label() }}</span></td>
                                    <td>
                                        @if ($s->is_active)
                                            <span class="badge bg-success">Aktif</span>
                                        @else
                                            <span class="badge bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-semibold text-primary">{{ $s->assignments_count }}</td>
                                    <td class="text-center fw-semibold text-success">{{ $s->reports_count }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Tidak ada data staff ditemukan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @else
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. WO</th>
                                <th>Judul Pekerjaan</th>
                                <th>Customer</th>
                                <th>Kategori</th>
                                <th>Tanggal Rencana</th>
                                <th>Teknisi</th>
                                <th>Status</th>
                                <th class="text-end">Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data as $wo)
                                <tr>
                                    <td><a href="{{ route('admin.work-orders.show', $wo) }}"
                                            class="fw-semibold text-decoration-none">{{ $wo->wo_number }}</a></td>
                                    <td>
                                        <div class="fw-semibold">{{ $wo->title }}</div>
                                        <small class="text-muted">{{ $wo->type?->name ?? '-' }}</small>
                                    </td>
                                    <td>{{ $wo->customer->name }}</td>
                                    <td>{{ $wo->serviceCategory->name }}</td>
                                    <td>{{ $wo->scheduled_date ? $wo->scheduled_date->format('d/m/Y') : '-' }}</td>
                                    <td>
                                        @forelse($wo->assignments as $assign)
                                            <span
                                                class="badge bg-secondary mb-1 d-inline-block">{{ $assign->technician->name }}</span>
                                        @empty
                                            <span class="text-muted small">Belum di-assign</span>
                                        @endforelse
                                    </td>
                                    <td><x-status-badge :status="$wo->status" /></td>
                                    <td class="text-end fw-semibold">Rp {{ number_format($wo->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted">Tidak ada data Work Order
                                        ditemukan</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                @endif
            </div>

            <div class="p-3 border-top">
                {{ $data->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
@endsection
