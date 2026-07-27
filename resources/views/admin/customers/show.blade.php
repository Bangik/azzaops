@extends('layouts.app')

@section('title', 'Detail Customer')
@section('page-title', 'Detail Data Customer')

@section('content')
<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Informasi Customer</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th width="40%">Tipe</th>
                        <td>
                            <span class="badge bg-{{ $customer->type->value === 'individual' ? 'info' : 'primary' }}">
                                {{ $customer->type->label() }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Nama</th>
                        <td>{{ $customer->name }}</td>
                    </tr>
                    @if($customer->type->value === 'business')
                    <tr>
                        <th>Perusahaan</th>
                        <td>{{ $customer->company_name ?? '-' }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th>PIC</th>
                        <td>{{ $customer->pic_name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Telepon</th>
                        <td>{{ $customer->phone }}</td>
                    </tr>
                    <tr>
                        <th>Telp Cadangan</th>
                        <td>{{ $customer->phone_alt ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $customer->email ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Kota</th>
                        <td>{{ $customer->city ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Market</th>
                        <td>{{ $customer->market ?? '-' }}</td>
                    </tr>
                </table>
                <div class="mt-3">
                    <strong>Alamat:</strong>
                    <p class="mb-0 text-muted">{{ $customer->address ?? '-' }}</p>
                </div>
                <div class="mt-3">
                    <strong>Catatan:</strong>
                    <p class="mb-0 text-muted">{{ $customer->notes ?? '-' }}</p>
                </div>
                <div class="mt-4 pt-3 border-top">
                    <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-pencil"></i> Edit Customer
                    </a>
                    <a href="{{ route('admin.customers.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riwayat Pekerjaan (Work Order)</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="wo-history-table">
                        <thead>
                            <tr>
                                <th>No. WO</th>
                                <th>Tipe</th>
                                <th>Status</th>
                                <th>Tgl Jadwal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($customer->workOrders as $wo)
                            <tr>
                                <td>{{ $wo->wo_number }}</td>
                                <td>{{ $wo->type->value }}</td>
                                <td>
                                    <span class="badge bg-{{ match($wo->status->value) {
                                        'pending' => 'warning',
                                        'assigned' => 'info',
                                        'in_progress' => 'primary',
                                        'completed' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'secondary'
                                    } }}">
                                        {{ $wo->status->value }}
                                    </span>
                                </td>
                                <td>{{ $wo->scheduled_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.work-orders.show', $wo) }}" class="btn btn-sm btn-info text-white">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center">Belum ada riwayat pekerjaan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
