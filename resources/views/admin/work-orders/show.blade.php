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
        @if($workOrder->type->code === 'checking' && $workOrder->status !== App\Enums\WorkOrderStatus::Completed && $workOrder->status !== App\Enums\WorkOrderStatus::Cancelled)
            <a href="{{ route('admin.work-orders.continue', $workOrder) }}" class="btn btn-success">
                <i class="bi bi-arrow-right-circle me-1"></i> Lanjutkan Pekerjaan
            </a>
        @endif
        @if($workOrder->status !== App\Enums\WorkOrderStatus::Completed && $workOrder->status !== App\Enums\WorkOrderStatus::Cancelled)
            <a href="{{ route('admin.work-orders.edit', $workOrder) }}" class="btn btn-warning">
                <i class="bi bi-pencil me-1"></i> Edit
            </a>
            @if(auth()->user()->role->value !== 'admin')
                <form action="{{ route('admin.work-orders.destroy', $workOrder) }}" method="POST" onsubmit="return confirmDelete('Yakin ingin membatalkan work order ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-x-circle me-1"></i> Batalkan
                    </button>
                </form>
            @endif
        @endif
        @if(auth()->user()->role->value === 'super_admin')
            <form action="{{ route('admin.work-orders.destroy', $workOrder) }}" method="POST" onsubmit="return confirmDelete('Yakin ingin MENGHAPUS work order ini secara permanen?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash me-1"></i> Hapus Permanen
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
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Tipe Pekerjaan</div>
                        <div class="fw-semibold">{{ $workOrder->type->name }}</div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Kategori Layanan</div>
                        <div class="fw-semibold">{{ $workOrder->serviceCategory->name }}</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Tanggal Rencana</div>
                        <div class="fw-semibold">
                            {{ $workOrder->scheduled_date ? $workOrder->scheduled_date->format('d M Y') : '-' }}
                            @if($workOrder->scheduled_time)
                                &nbsp;<span class="text-muted small"><i class="bi bi-clock"></i> {{ date('H:i', strtotime($workOrder->scheduled_time)) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="text-muted small">Urutan Pekerjaan</div>
                        <div class="fw-semibold">{{ $workOrder->job_order ?? '-' }}</div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="text-muted small">Dibuat Oleh</div>
                        <div class="fw-semibold">{{ $workOrder->creator->name }} <span class="text-muted small">({{ $workOrder->created_at->format('d/m/Y H:i') }})</span></div>
                    </div>
                    <div class="col-md-6">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="text-muted small">Lokasi Pengerjaan</div>
                    <div class="fw-semibold">
                        {{ $workOrder->location }}
                        @if($workOrder->gmaps_link)
                            <a href="{{ $workOrder->gmaps_link }}" target="_blank" class="ms-2 badge text-bg-light border text-decoration-none">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Google Maps
                            </a>
                        @endif
                    </div>
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
                <h5 class="mb-0">Laporan Pekerjaan</h5>
            </div>
            <div class="card-body">
                @foreach($workOrder->reports as $report)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                        <div>
                            <span class="fw-semibold text-primary">{{ $report->technician->name }}</span>
                            <span class="text-muted small"> | {{ $report->submitted_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#editReportCollapse-{{ $report->id }}" title="Edit Laporan" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                            <i class="bi bi-pencil"></i> Edit Laporan
                        </button>
                    </div>

                    {{-- Collapse Form Edit Laporan --}}
                    <div class="collapse mb-3" id="editReportCollapse-{{ $report->id }}">
                        <form action="{{ route('admin.work-orders.update-report', [$workOrder, $report]) }}" method="POST" class="bg-light p-3 border rounded">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="findings-{{ $report->id }}" class="form-label small fw-bold">Temuan Lapangan</label>
                                <textarea class="form-control form-control-sm" id="findings-{{ $report->id }}" name="findings" rows="3" required>{{ $report->findings }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="work_done-{{ $report->id }}" class="form-label small fw-bold">Pekerjaan yang Dilakukan</label>
                                <textarea class="form-control form-control-sm" id="work_done-{{ $report->id }}" name="work_done" rows="3" required>{{ $report->work_done }}</textarea>
                            </div>
                            <div class="mb-3">
                                <label for="recommendations-{{ $report->id }}" class="form-label small fw-bold">Rekomendasi</label>
                                <textarea class="form-control form-control-sm" id="recommendations-{{ $report->id }}" name="recommendations" rows="3">{{ $report->recommendations }}</textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">Simpan Laporan</button>
                                <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="collapse" data-bs-target="#editReportCollapse-{{ $report->id }}">Batal</button>
                            </div>
                        </form>
                    </div>

                    <div class="mb-2">
                        <div class="text-muted small fw-bold">Temuan</div>
                        <div class="text-dark bg-light p-2 rounded" style="white-space: pre-line;">{{ $report->findings }}</div>
                    </div>
                    <div class="mb-2">
                        <div class="text-muted small fw-bold">Pekerjaan Dilakukan</div>
                        <div class="text-dark bg-light p-2 rounded" style="white-space: pre-line;">{{ $report->work_done }}</div>
                    </div>
                    @if($report->recommendations)
                    <div class="mb-2">
                        <div class="text-muted small fw-bold">Rekomendasi</div>
                        <div class="text-dark bg-light p-2 rounded" style="white-space: pre-line;">{{ $report->recommendations }}</div>
                    </div>
                    @endif
                    @if($report->photos->count())
                    <div class="d-flex gap-3 flex-wrap mt-3">
                        @foreach($report->photos as $photo)
                        <div class="border rounded p-1 d-flex flex-column align-items-center bg-white" style="width: 100px;">
                            <a href="{{ $photo->photo_url }}" target="_blank" class="position-relative" title="{{ $photo->photo_type->label() }}">
                                <img src="{{ $photo->photo_url }}" alt="{{ $photo->caption ?? $photo->photo_type->label() }}" style="width:88px;height:88px;object-fit:cover;border-radius:4px;">
                                <span class="badge bg-dark position-absolute bottom-0 start-0 m-1" style="font-size:0.6rem;">{{ $photo->photo_type->label() }}</span>
                            </a>
                            <a href="{{ $photo->photo_url }}" download class="btn btn-sm btn-outline-primary mt-1 w-100 py-0" style="font-size: 0.75rem;">
                                <i class="bi bi-download"></i> Unduh
                            </a>
                        </div>
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
                        <a href="{{ route('admin.invoices.pdf', $workOrder->invoice) }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-download me-1"></i> Download Invoice (PDF)
                        </a>
                        <a href="{{ route('admin.work-orders.invoice-report-pdf', $workOrder) }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Download Invoice + Laporan (PDF)
                        </a>
                    @else
                        <button class="btn btn-outline-secondary btn-sm text-start" disabled>
                            <i class="bi bi-receipt me-1"></i> Belum ada Invoice
                        </button>
                    @endif

                    @if($workOrder->reports->count())
                        <a href="{{ route('admin.work-orders.report-pdf', $workOrder) }}" class="btn btn-outline-success btn-sm text-start">
                            <i class="bi bi-file-earmark-pdf me-1"></i> Download Laporan Kerja (PDF)
                        </a>
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
