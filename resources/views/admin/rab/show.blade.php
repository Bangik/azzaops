@extends('layouts.app')

@section('title', 'Detail RAB')
@section('page-title', 'Detail RAB')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $rab->rab_number }}</h1>
        <p class="text-muted mb-0">{{ $rab->title }}</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if($rab->status === App\Enums\RabStatus::Draft)
            <form action="{{ route('admin.rab.send', $rab) }}" method="POST">
                @csrf @method('PUT')
                <button class="btn btn-info text-white"><i class="bi bi-send me-1"></i> Tandai Terkirim</button>
            </form>
        @endif
        @if(in_array($rab->status, [App\Enums\RabStatus::Draft, App\Enums\RabStatus::Sent, App\Enums\RabStatus::Revised]))
            <form action="{{ route('admin.rab.approve', $rab) }}" method="POST">
                @csrf @method('PUT')
                <button class="btn btn-success"><i class="bi bi-check2-circle me-1"></i> Setujui RAB</button>
            </form>
        @endif
        <a href="{{ route('admin.rab.preview', $rab) }}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-eye me-1"></i> Preview</a>
        <a href="{{ route('admin.rab.pdf', $rab) }}" class="btn btn-danger"><i class="bi bi-file-pdf me-1"></i> Download PDF</a>
        <a href="{{ route('admin.rab.index') }}" class="btn btn-secondary">Kembali</a>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Item RAB</h5>
                <x-status-badge :status="$rab->status" />
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th>Deskripsi</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Harga</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rab->items as $item)
                            <tr>
                                <td><span class="badge bg-secondary">{{ $item->category ?: 'Lainnya' }}</span></td>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }} {{ $item->unit }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="4" class="text-end fw-bold">Grand Total</td>
                                <td class="text-end fw-bold">Rp {{ number_format($rab->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Informasi</h5></div>
            <div class="card-body">
                <div class="mb-2"><span class="text-muted small">Customer</span><div class="fw-semibold">{{ $rab->customer->name }}</div></div>
                <div class="mb-2"><span class="text-muted small">Work Order</span><div class="fw-semibold"><a href="{{ route('admin.work-orders.show', $rab->workOrder) }}">{{ $rab->workOrder->wo_number }}</a></div></div>
                <div class="mb-2"><span class="text-muted small">Berlaku s/d</span><div class="fw-semibold">{{ $rab->valid_until?->format('d/m/Y') ?: '-' }}</div></div>
                <div class="mb-0"><span class="text-muted small">Dibuat oleh</span><div class="fw-semibold">{{ $rab->creator->name }}</div></div>
            </div>
        </div>
    </div>
</div>
@endsection
