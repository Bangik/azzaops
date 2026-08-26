@extends('layouts.app')

@section('title', 'Invoice')
@section('page-title', 'Invoice')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Invoice</h1>
            <p class="text-muted mb-0">Kelola tagihan pelanggan dari work order.</p>
        </div>
        <a href="{{ route('admin.reports.index', ['type' => 'invoices']) }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Laporan Invoice
        </a>
    </div>

    <div class="filter-bar">
        <form action="{{ route('admin.invoices.index') }}" method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label small">Cari Invoice / Customer</label>
                <input type="text" name="q" class="form-control form-control-sm" value="{{ request('q') }}"
                    placeholder="No. invoice atau nama customer">
            </div>
            <div class="col-md-4">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    @foreach (App\Enums\InvoiceStatus::cases() as $status)
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
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Invoice</h5>
            <span class="badge text-bg-light border">Total: {{ $invoices->total() }}</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>No. Invoice</th>
                            <th>Customer</th>
                            <th>Work Order</th>
                            <th class="text-end">Total</th>
                            <th>Pembayaran</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.invoices.show', $invoice) }}"
                                        class="fw-semibold text-decoration-none">
                                        {{ $invoice->invoice_number }}
                                    </a>
                                </td>
                                <td>{{ $invoice->customer->name }}</td>
                                <td>{{ $invoice->workOrder->wo_number }}</td>
                                <td class="text-end">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                <td><x-status-badge :status="$invoice->payment_status" /></td>
                                <td><x-status-badge :status="$invoice->status" /></td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.invoices.show', $invoice) }}"
                                            class="btn btn-sm btn-info text-white btn-action"><i class="bi bi-eye"></i></a>
                                        <a href="{{ route('admin.invoices.pdf', $invoice) }}"
                                            class="btn btn-sm btn-danger btn-action" title="Download PDF"><i
                                                class="bi bi-file-pdf"></i></a>
                                        @if ($invoice->payment_status === App\Enums\PaymentStatus::Paid)
                                            <a href="{{ route('admin.invoices.receipt', $invoice) }}"
                                                class="btn btn-sm btn-success btn-action" title="Download Kwitansi"><i
                                                    class="bi bi-file-earmark-check"></i></a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <i class="bi bi-receipt"></i>
                                        <h6>Belum ada invoice</h6>
                                        <p class="small text-muted">Generate invoice dari detail work order yang sudah
                                            dilaporkan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-footer">
                <div class="pagination-info">
                    Menampilkan {{ $invoices->firstItem() ?? 0 }} - {{ $invoices->lastItem() ?? 0 }} dari
                    {{ $invoices->total() }} data
                </div>
                {{ $invoices->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
@endsection
