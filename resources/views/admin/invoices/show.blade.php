@extends('layouts.app')

@section('title', 'Detail Invoice')
@section('page-title', 'Detail Invoice')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $invoice->invoice_number }}</h1>
        <p class="text-muted mb-0">{{ $invoice->customer->name }} — WO {{ $invoice->workOrder->wo_number }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.invoices.preview', $invoice) }}" target="_blank" class="btn btn-outline-primary">
            <i class="bi bi-eye me-1"></i> Preview PDF
        </a>
        <a href="{{ route('admin.invoices.pdf', $invoice) }}" class="btn btn-danger">
            <i class="bi bi-file-pdf me-1"></i> Download PDF
        </a>
        <a href="{{ route('admin.invoices.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Rincian Invoice</h5>
                <div>
                    <x-status-badge :status="$invoice->status" />
                    <x-status-badge :status="$invoice->payment_status" />
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Deskripsi</th>
                                <th class="text-center">Qty</th>
                                <th>Satuan</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td>{{ $item->unit ?: '-' }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end">Subtotal</td>
                                <td class="text-end">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
                            </tr>
                            @if($invoice->discount_value > 0)
                            <tr>
                                <td colspan="4" class="text-end">Diskon ({{ $invoice->discount_type === 'percent' ? intval($invoice->discount_value).'%' : 'Potongan Langsung' }})</td>
                                <td class="text-end">-Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            @if($invoice->tax_percentage > 0)
                            <tr>
                                <td colspan="4" class="text-end">PPN ({{ intval($invoice->tax_percentage) }}%)</td>
                                <td class="text-end">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Grand Total</td>
                                <td class="text-end fw-bold">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h5 class="mb-0">Info Pembayaran</h5></div>
            <div class="card-body">
                <div class="mb-2"><span class="text-muted small">Sudah dibayar</span><div class="fw-semibold">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</div></div>
                <div class="mb-2"><span class="text-muted small">Metode</span><div class="fw-semibold">{{ $invoice->payment_method ?: '-' }}</div></div>
                <div class="mb-2"><span class="text-muted small">Akun Keuangan</span><div class="fw-semibold">{{ $invoice->financialAccount ? $invoice->financialAccount->name : '-' }}</div></div>
                <div class="mb-0"><span class="text-muted small">Tanggal bayar</span><div class="fw-semibold">{{ $invoice->payment_date?->format('d/m/Y') ?: '-' }}</div></div>
            </div>
        </div>

        @if($invoice->payment_status !== App\Enums\PaymentStatus::Paid)
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Catat Pembayaran</h5></div>
            <div class="card-body">
                <form action="{{ route('admin.invoices.pay', $invoice) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Jumlah Dibayar</label>
                        <input type="number" name="paid_amount" class="form-control" value="{{ old('paid_amount', intval($invoice->total - $invoice->paid_amount)) }}" min="1" max="{{ intval($invoice->total) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tanggal Bayar</label>
                        <input type="text" name="payment_date" class="form-control datepicker" value="{{ old('payment_date', now()->format('Y-m-d')) }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Metode</label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Akun Keuangan</label>
                        <select name="financial_account_id" class="form-select" required>
                            <option value="">Pilih Akun Keuangan</option>
                            @foreach(\App\Models\FinancialAccount::active()->get() as $account)
                                <option value="{{ $account->id }}" {{ old('financial_account_id', $invoice->financial_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-success w-100"><i class="bi bi-check2-circle me-1"></i> Tandai Dibayar</button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
