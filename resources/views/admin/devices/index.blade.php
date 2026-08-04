@extends('layouts.app')

@section('title', 'Device Staff')
@section('page-title', 'Device Staff')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Device Staff</h1>
        <p class="text-muted mb-0">Kelola dan pantau informasi perangkat mobile yang digunakan staff.</p>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Daftar Perangkat</h5>
            <p class="text-muted small mb-0">Menampilkan informasi detail device dan sesi login aktif staff.</p>
        </div>
        <span class="badge text-bg-light border">Total: {{ $devices->total() }}</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Staff</th>
                        <th>Device ID</th>
                        <th>Platform</th>
                        <th>Brand & Model</th>
                        <th>App Version (Build)</th>
                        <th>OS Version</th>
                        <th>Resolution</th>
                        <th>Network</th>
                        <th>Last Sync</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($devices as $device)
                    <tr>
                        <td>
                            <strong>{{ $device->user->name ?? 'Unknown' }}</strong>
                            <div class="text-muted small">{{ $device->user->email ?? '-' }}</div>
                        </td>
                        <td><code class="small">{{ $device->device_id }}</code></td>
                        <td>
                            @if($device->platform === 'android')
                                <span class="badge bg-success"><i class="bi bi-android me-1"></i> Android</span>
                            @else
                                <span class="badge bg-dark"><i class="bi bi-apple me-1"></i> iOS</span>
                            @endif
                        </td>
                        <td>
                            {{ $device->device_brand ?? '-' }} 
                            <span class="text-muted small">({{ $device->device_model ?? '-' }})</span>
                        </td>
                        <td>
                            {{ $device->app_version }} 
                            <span class="text-muted small">({{ $device->build_number }})</span>
                        </td>
                        <td>{{ $device->os_version ?? '-' }}</td>
                        <td>{{ $device->screen_resolution ?? '-' }}</td>
                        <td>{{ $device->network_type ?? '-' }}</td>
                        <td>{{ $device->updated_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada perangkat yang terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="pagination-footer">
            <div class="pagination-info">
                Menampilkan {{ $devices->firstItem() ?? 0 }} - {{ $devices->lastItem() ?? 0 }} dari {{ $devices->total() }} data
            </div>
            {{ $devices->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
