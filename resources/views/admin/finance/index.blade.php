@extends('layouts.app')

@section('title', 'Laporan Keuangan')
@section('page-title', 'Laporan Keuangan')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Keuangan</h1>
            <p class="text-muted mb-0">Monitor pemasukan, pengeluaran, dan neraca operasional.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.index', ['type' => 'finance']) }}" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Laporan Keuangan
            </a>
            <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i> Catat Pengeluaran
            </a>
        </div>
    </div>

    {{-- Filter Period --}}
    <div class="filter-bar">
        <form action="{{ route('admin.finance.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small">Periode Laporan</label>
                <select name="period" id="period-select" class="form-select form-select-sm">
                    <option value="today" @selected(request('period') === 'today')>Hari Ini</option>
                    <option value="week" @selected(request('period') === 'week')>Minggu Ini</option>
                    <option value="month" @selected(request('period') === 'month' || !request('period'))>Bulan Ini</option>
                    <option value="custom" @selected(request('period') === 'custom')>Kustom Tanggal</option>
                </select>
            </div>
            <div class="col-md-3 custom-date-group d-none">
                <label class="form-label small">Dari Tanggal</label>
                <input type="text" name="from" class="form-control form-control-sm datepicker"
                    value="{{ $from }}">
            </div>
            <div class="col-md-3 custom-date-group d-none">
                <label class="form-label small">Sampai Tanggal</label>
                <input type="text" name="to" class="form-control form-control-sm datepicker"
                    value="{{ $to }}">
            </div>
            <div class="col-md-3 d-grid">
                <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Pemasukan</div>
                        <div class="stat-value text-success">Rp {{ number_format($summary['total_income'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-danger bg-opacity-10 text-danger me-3">
                        <i class="bi bi-graph-down-arrow"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Pengeluaran</div>
                        <div class="stat-value text-danger">Rp {{ number_format($summary['total_expense'], 0, ',', '.') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                        <i class="bi bi-piggy-bank"></i>
                    </div>
                    <div>
                        <div class="stat-label">Neraca Saldo</div>
                        <div class="stat-value text-info">Rp {{ number_format($summary['balance'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card stat-card border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div>
                        <div class="stat-label">Cost Percentage</div>
                        <div class="stat-value text-warning">{{ $summary['cost_percentage'] }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Financial Tables Tabs --}}
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0">Alur Kas (Semua Transaksi)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Deskripsi / PIC</th>
                                    <th class="text-end">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $t)
                                    <tr>
                                        <td>{{ $t->transaction_date->format('d/m/Y') }}</td>
                                        <td>
                                            <span
                                                class="badge {{ $t->type->value === 'income' ? 'bg-success' : 'bg-danger' }}">
                                                {{ $t->category ? $t->category->name : ($t->type->value === 'income' ? 'Pembayaran Jasa' : 'Lain-lain') }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $t->description }}</div>
                                            @if ($t->invoice)
                                                <small class="text-muted">Invoice: <a
                                                        href="{{ route('admin.invoices.show', $t->invoice) }}">{{ $t->invoice->invoice_number }}</a></small>
                                            @endif
                                        </td>
                                        <td
                                            class="text-end fw-semibold {{ $t->type->value === 'income' ? 'text-success' : 'text-danger' }}">
                                            {{ $t->type->value === 'income' ? '+' : '-' }}Rp
                                            {{ number_format($t->amount, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada transaksi di
                                            periode ini</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        {{ $transactions->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pengeluaran Operasional</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0 align-middle">
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Deskripsi</th>
                                    <th class="text-end">Nominal</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expenses as $e)
                                    <tr>
                                        <td>{{ $e->expense_date->format('d/m/Y') }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $e->description }}</div>
                                            @if ($e->pic)
                                                <small class="text-muted">PIC: {{ $e->pic }}</small>
                                            @endif
                                            <small class="text-muted">{{ $e->category->name }}</small>
                                            @if ($e->receipt_photo)
                                                <div class="mt-1"><a href="{{ asset($e->receipt_photo) }}"
                                                        target="_blank"
                                                        class="badge text-bg-light border text-decoration-none"><i
                                                            class="bi bi-image me-1"></i> Struk</a></div>
                                            @endif
                                        </td>
                                        <td class="text-end text-danger fw-semibold">Rp
                                            {{ number_format($e->amount, 0, ',', '.') }}</td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('admin.expenses.edit', $e) }}"
                                                    class="btn btn-sm btn-warning btn-action"><i
                                                        class="bi bi-pencil"></i></a>
                                                <form action="{{ route('admin.expenses.destroy', $e) }}" method="POST"
                                                    onsubmit="return confirmDelete('Hapus catatan pengeluaran ini?')">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-sm btn-danger btn-action"><i
                                                            class="bi bi-trash"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-4">Belum ada pengeluaran
                                            dicatat</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="p-3 border-top">
                        {{ $expenses->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            function toggleDateInputs() {
                if ($('#period-select').val() === 'custom') {
                    $('.custom-date-group').removeClass('d-none');
                } else {
                    $('.custom-date-group').addClass('d-none');
                }
            }
            $('#period-select').on('change', toggleDateInputs);
            toggleDateInputs();
        });
    </script>
@endpush
