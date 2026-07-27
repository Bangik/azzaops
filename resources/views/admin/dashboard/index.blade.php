@extends('layouts.app')

@section('title', 'Dashboard - AzzaOps')
@section('page-title', 'Dashboard')

@section('breadcrumb')
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
</nav>
@endsection

@section('content')
{{-- Stat Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3">
                    <i class="bi bi-clipboard-plus"></i>
                </div>
                <div>
                    <div class="stat-label">WO Baru & Ditugaskan</div>
                    <div class="stat-value">{{ $woNew ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3">
                    <i class="bi bi-arrow-repeat"></i>
                </div>
                <div>
                    <div class="stat-label">Sedang Dikerjakan</div>
                    <div class="stat-value">{{ $woInProgress ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div>
                    <div class="stat-label">Total Selesai</div>
                    <div class="stat-value">{{ $woCompleted ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card border-0">
            <div class="card-body d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3">
                    <i class="bi bi-cash-stack"></i>
                </div>
                <div>
                    <div class="stat-label">Pemasukan Bulan Ini</div>
                    <div class="stat-value">Rp {{ number_format($incomeThisMonth ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Charts Section --}}
<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card border-0 h-100">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold">Tren Keuangan</h5>
                    <p class="text-muted small mb-0">Rangkuman 6 bulan terakhir</p>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 300px; position: relative;">
                    <canvas id="financeChart"></canvas>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card border-0 h-100">
            <div class="card-header border-0">
                <h5 class="mb-1 fw-bold">Jenis Layanan</h5>
                <p class="text-muted small mb-0">Distribusi WO bulan ini</p>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                @if(array_sum($typeValues) > 0)
                <div style="height: 250px; width: 100%; position: relative;">
                    <canvas id="typeChart"></canvas>
                </div>
                @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-pie-chart d-block fs-2 mb-2 opacity-50"></i>
                    <p class="small mb-0">Belum ada data WO dibuat bulan ini</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Action Required & Recent Activity --}}
<div class="row g-3">
    <div class="col-md-12">
        <div class="card border-0">
            <div class="card-header border-0 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1 fw-bold">Perlu Tindakan Segera</h5>
                    <p class="text-muted small mb-0">Daftar perintah kerja pending atau laporan baru masuk</p>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>No. WO</th>
                                <th>Judul Pekerjaan</th>
                                <th>Customer</th>
                                <th>Tanggal Rencana</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentActions as $action)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.work-orders.show', $action) }}" class="fw-semibold text-decoration-none">
                                        {{ $action->wo_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $action->title }}</div>
                                    <small class="text-muted">{{ $action->serviceCategory->name }}</small>
                                </td>
                                <td>
                                    <div>{{ $action->customer->name }}</div>
                                    @if($action->customer->company_name)
                                        <small class="text-muted">{{ $action->customer->company_name }}</small>
                                    @endif
                                </td>
                                <td>{{ $action->scheduled_date ? $action->scheduled_date->format('d/m/Y') : '-' }}</td>
                                <td><x-status-badge :status="$action->status" /></td>
                                <td>
                                    @if($action->status === App\Enums\WorkOrderStatus::Pending)
                                        <a href="{{ route('admin.work-orders.show', $action) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-person-plus me-1"></i> Assign Teknisi
                                        </a>
                                    @elseif($action->status === App\Enums\WorkOrderStatus::Reported)
                                        <a href="{{ route('admin.invoices.create', ['work_order_id' => $action->id]) }}" class="btn btn-sm btn-success">
                                            <i class="bi bi-receipt me-1"></i> Bikin Invoice
                                        </a>
                                    @else
                                        <a href="{{ route('admin.work-orders.show', $action) }}" class="btn btn-sm btn-outline-secondary">
                                            Lihat
                                        </a>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="bi bi-check-circle-fill text-success fs-3 mb-2 d-block"></i>
                                    Semua tugas aman! Tidak ada tindakan mendesak diperlukan.
                                </td>
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

@push('scripts')
@if(count($months) > 0)
<script>
$(function() {
    // 1. Finance Trend Chart
    const financeCtx = document.getElementById('financeChart').getContext('2d');
    new Chart(financeCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Pemasukan (IDR)',
                    data: {!! json_encode($incomeData) !!},
                    backgroundColor: 'rgba(5, 150, 105, 0.75)', // emerald-600
                    borderColor: 'rgb(5, 150, 105)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Pengeluaran (IDR)',
                    data: {!! json_encode($expenseData) !!},
                    backgroundColor: 'rgba(220, 38, 38, 0.75)', // red-600
                    borderColor: 'rgb(220, 38, 38)',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        boxWidth: 12,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.raw !== null) {
                                label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.raw);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value, index, values) {
                            return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumSignificantDigits: 3 }).format(value);
                        }
                    }
                }
            }
        }
    });

    // 2. Type Distribution Chart
    @if(array_sum($typeValues) > 0)
    const typeCtx = document.getElementById('typeChart').getContext('2d');
    new Chart(typeCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($typeLabels) !!},
            datasets: [{
                data: {!! json_encode($typeValues) !!},
                backgroundColor: [
                    'rgba(217, 119, 6, 0.8)',   // amber-600 (checking)
                    'rgba(2, 132, 199, 0.8)',   // sky-600 (service)
                    'rgba(5, 150, 105, 0.8)',   // emerald-600 (installation)
                    'rgba(100, 116, 139, 0.8)'  // slate-500 (maintenance)
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 15
                    }
                }
            },
            cutout: '65%'
        }
    });
    @endif
});
</script>
@endif
@endpush
