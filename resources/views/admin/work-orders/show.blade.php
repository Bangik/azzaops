@extends('layouts.app')

@section('title', 'Detail Work Order')
@section('page-title', 'Detail Work Order')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">{{ $workOrder->wo_number }}</h1>
        <p class="text-muted mb-0">{{ $workOrder->title }}</p>
    </div>
    <div class="d-flex gap-2">
        @if($workOrder->type === App\Enums\WorkOrderType::Checking && $workOrder->status !== App\Enums\WorkOrderStatus::Completed && $workOrder->status !== App\Enums\WorkOrderStatus::Cancelled)
            <a href="{{ route('admin.work-orders.continue', $workOrder) }}" class="btn btn-success">
                <i class="bi bi-arrow-right-circle me-1"></i> Lanjutkan Pekerjaan
            </a>
        @endif
        @if($workOrder->status !== App\Enums\WorkOrderStatus::Completed && $workOrder->status !== App\Enums\WorkOrderStatus::Cancelled)
            <a href="{{ route('admin.work-orders.edit', $workOrder) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            <form action="{{ route('admin.work-orders.destroy', $workOrder) }}" method="POST" onsubmit="return confirmDelete('Yakin ingin membatalkan work order ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-x-circle me-1"></i> Batalkan
                </button>
            </form>
        @endif
        <a href="{{ route('admin.work-orders.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- Left Column: Detail Info --}}
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Informasi Pekerjaan</h5>
                <div>
                    <x-status-badge :status="$workOrder->status" />
                    <span class="badge bg-{{ $workOrder->priority->color() }} ms-1">{{ $workOrder->priority->label() }}</span>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Tipe Pekerjaan</div>
                        <div class="fw-semibold">{{ $workOrder->type->label() }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Kategori Layanan</div>
                        <div class="fw-semibold">{{ $workOrder->serviceCategory->name }}</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal Rencana</div>
                        <div class="fw-semibold">{{ $workOrder->scheduled_date ? $workOrder->scheduled_date->format('d M Y') : '-' }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Dibuat Oleh</div>
                        <div class="fw-semibold">{{ $workOrder->creator->name }} <span class="text-muted small">({{ $workOrder->created_at->format('d/m/Y H:i') }})</span></div>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Lokasi Pengerjaan</div>
                    <div class="fw-semibold">{{ $workOrder->location }}</div>
                </div>
                <div class="mb-0">
                    <div class="text-muted small">Deskripsi / Keluhan</div>
                    <div class="fw-semibold" style="white-space: pre-line;">{{ $workOrder->description ?: '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Items --}}
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Item Pekerjaan & Material</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 align-middle">
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
                            @forelse($workOrder->items as $item)
                            <tr>
                                <td>{{ $item->description }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td>{{ $item->unit ?: '-' }}</td>
                                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="text-end fw-semibold">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada item pekerjaan</td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if($workOrder->items->count())
                        <tfoot>
                            <tr class="table-light">
                                <td colspan="4" class="text-end fw-bold">Grand Total</td>
                                <td class="text-end fw-bold">Rp {{ number_format($workOrder->total, 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        {{-- Reports --}}
        @if($workOrder->reports->count())
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Laporan Teknisi</h5>
            </div>
            <div class="card-body">
                @foreach($workOrder->reports as $report)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <div class="fw-semibold">{{ $report->technician->name }}</div>
                        <div class="text-muted small">{{ $report->submitted_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">Temuan</div>
                        <div>{{ $report->findings }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small">Pekerjaan Dilakukan</div>
                        <div>{{ $report->work_done }}</div>
                    </div>
                    @if($report->recommendations)
                    <div class="mb-2">
                        <div class="text-muted small">Rekomendasi</div>
                        <div>{{ $report->recommendations }}</div>
                    </div>
                    @endif
                    @if($report->photos->count())
                    <div class="d-flex gap-2 flex-wrap mt-2">
                        @foreach($report->photos as $photo)
                        <a href="{{ $photo->photo_url }}" target="_blank" class="border rounded p-1 position-relative" title="{{ $photo->photo_type->label() }}">
                            <img src="{{ $photo->photo_url }}" alt="{{ $photo->caption ?? $photo->photo_type->label() }}" style="width:80px;height:80px;object-fit:cover;border-radius:4px;">
                            <span class="badge bg-dark position-absolute bottom-0 start-0 m-1" style="font-size:0.6rem;">{{ $photo->photo_type->label() }}</span>
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Right Column: Customer + Assignment --}}
    <div class="col-lg-4">
        {{-- Customer Card --}}
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Customer</h5>
            </div>
            <div class="card-body">
                <div class="fw-semibold mb-1">{{ $workOrder->customer->name }}</div>
                @if($workOrder->customer->company_name)
                    <div class="text-muted small mb-2">{{ $workOrder->customer->company_name }}</div>
                @endif
                <div class="small mb-1">
                    <i class="bi bi-telephone me-1"></i> {{ $workOrder->customer->phone }}
                </div>
                @if($workOrder->customer->address)
                <div class="small text-muted">
                    <i class="bi bi-geo-alt me-1"></i> {{ $workOrder->customer->address }}
                </div>
                @endif
                <a href="{{ route('admin.customers.show', $workOrder->customer) }}" class="btn btn-sm btn-outline-primary mt-3 w-100">
                    Lihat Profil Customer
                </a>
            </div>
        </div>

        {{-- Assignment Card --}}
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Penugasan Teknisi</h5>
            </div>
            <div class="card-body">
                @if($workOrder->assignments->count())
                    <div class="mb-3">
                        @foreach($workOrder->assignments as $assignment)
                        <div class="d-flex justify-content-between align-items-center border rounded p-2 mb-2">
                            <div>
                                <div class="fw-semibold small">{{ $assignment->technician->name }}</div>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    {{ $assignment->assigned_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                            <x-status-badge :status="$assignment->status" />
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-muted small mb-3 text-center py-2">
                        <i class="bi bi-person-x d-block fs-4 mb-1 opacity-50"></i>
                        Belum ada teknisi ditugaskan
                    </div>
                @endif

                @if(!in_array($workOrder->status, [App\Enums\WorkOrderStatus::Completed, App\Enums\WorkOrderStatus::Cancelled]))
                <hr>
                <form action="{{ route('admin.work-orders.assign', $workOrder) }}" method="POST">
                    @csrf
                    <label class="form-label small fw-semibold">{{ $workOrder->assignments->count() ? 'Re-assign / Tambah Teknisi' : 'Pilih Teknisi' }}</label>
                    <div class="mb-3" style="max-height:180px;overflow-y:auto;">
                        @forelse($technicians as $tech)
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="technician_ids[]" value="{{ $tech->id }}" id="tech_{{ $tech->id }}"
                                {{ $workOrder->assignments->contains('technician_id', $tech->id) ? 'checked' : '' }}>
                            <label class="form-check-label small" for="tech_{{ $tech->id }}">
                                {{ $tech->name }}
                            </label>
                        </div>
                        @empty
                        <div class="text-muted small">Tidak ada teknisi aktif</div>
                        @endforelse
                    </div>
                    @error('technician_ids')
                        <div class="text-danger small mb-2">{{ $message }}</div>
                    @enderror
                    <button type="submit" class="btn btn-primary btn-sm w-100">
                        <i class="bi bi-person-check me-1"></i> Tugaskan
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Related Docs --}}
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Dokumen Terkait</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    @if($workOrder->invoice)
                        <a href="{{ route('admin.invoices.show', $workOrder->invoice) }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-receipt me-1"></i> Invoice: {{ $workOrder->invoice->invoice_number }}
                        </a>
                    @else
                        <button class="btn btn-outline-secondary btn-sm text-start" disabled>
                            <i class="bi bi-receipt me-1"></i> Belum ada Invoice
                        </button>
                    @endif

                    @if($workOrder->rab)
                        <a href="{{ route('admin.rab.show', $workOrder->rab) }}" class="btn btn-outline-primary btn-sm text-start">
                            <i class="bi bi-calculator me-1"></i> RAB: {{ $workOrder->rab->rab_number }}
                        </a>
                    @else
                        <button class="btn btn-outline-secondary btn-sm text-start" disabled>
                            <i class="bi bi-calculator me-1"></i> Belum ada RAB
                        </button>
                    @endif

                    @if($workOrder->parentWorkOrder)
                        <a href="{{ route('admin.work-orders.show', $workOrder->parentWorkOrder) }}" class="btn btn-outline-info btn-sm text-start">
                            <i class="bi bi-link-45deg me-1"></i> WO Induk: {{ $workOrder->parentWorkOrder->wo_number }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
