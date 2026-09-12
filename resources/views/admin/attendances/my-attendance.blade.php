@extends('layouts.app')

@section('title', 'Presensi Saya')
@section('page-title', 'Presensi Saya')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Presensi Saya</h1>
            <p class="text-muted mb-0">Lakukan presensi masuk dan pulang harian.</p>
        </div>
    </div>

    @if ($errors->has('location'))
        <div class="alert alert-danger">{{ $errors->first('location') }}</div>
    @endif

    <div class="row">
        {{-- Kartu Presensi Hari Ini --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Hari Ini —
                        {{ now()->translatedFormat('l, d F Y') }}</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">Jadwal Kerja</small>
                        <div class="fw-semibold">{{ $schedule['work_start_time'] }} - {{ $schedule['work_end_time'] }}</div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <small class="text-muted d-block">Lokasi Presensi</small>
                                <span class="small">Lingkaran menunjukkan area yang diizinkan.</span>
                            </div>
                            <button type="button" id="show-my-location" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-crosshair me-1"></i> Posisi Saya
                            </button>
                        </div>
                        <div id="attendance-view-map" class="attendance-view-map"
                            data-latitude="{{ $schedule['attendance_latitude'] }}"
                            data-longitude="{{ $schedule['attendance_longitude'] }}"
                            data-radius="{{ $schedule['attendance_radius_meters'] }}"></div>
                        <div id="attendance-map-status" class="small text-muted mt-2">
                            <i class="bi bi-info-circle me-1"></i> Memuat lokasi presensi...
                        </div>
                    </div>

                    @if ($today)
                        <div class="row mb-3">
                            <div class="col-6">
                                <small class="text-muted">Jam Masuk</small>
                                <div class="fw-semibold fs-5 text-success">
                                    <i class="bi bi-box-arrow-in-right me-1"></i>
                                    {{ $today->check_in ?? '-' }}
                                </div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Jam Pulang</small>
                                <div class="fw-semibold fs-5 text-danger">
                                    <i class="bi bi-box-arrow-right me-1"></i>
                                    {{ $today->check_out ?? '-' }}
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted">Status</small>
                            <div>
                                <span class="badge bg-{{ $today->status->color() }} fs-6">
                                    {{ $today->status->label() }}
                                </span>
                            </div>
                        </div>
                        @if ($today->work_duration)
                            <div class="mb-3">
                                <small class="text-muted">Durasi Kerja</small>
                                <div class="fw-semibold">{{ $today->work_duration }}</div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-3 text-muted">
                            <i class="bi bi-clock-history fs-1 d-block mb-2"></i>
                            Belum ada presensi hari ini.
                        </div>
                    @endif

                    {{-- Action Buttons --}}
                    <div class="d-grid gap-2 mt-3">
                        @if (!$today || !$today->check_in)
                            <div id="location-permission-status" class="alert alert-warning small mb-0">
                                <i class="bi bi-geo-alt me-1"></i>
                                Izin lokasi diperlukan untuk melakukan presensi.
                            </div>
                            <button type="button" id="request-location-permission" class="btn btn-outline-secondary">
                                <i class="bi bi-crosshair me-1"></i> Aktifkan Lokasi
                            </button>
                            <form action="{{ route('admin.attendances.check-in') }}" method="POST"
                                class="location-attendance-form">
                                @csrf
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                                <div class="mb-2">
                                    <input type="text" name="notes" class="form-control form-control-sm"
                                        placeholder="Catatan (opsional)">
                                </div>
                                <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Konfirmasi presensi masuk?')">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Presensi Masuk
                                </button>
                            </form>
                        @elseif (!$today->check_out)
                            <div id="location-permission-status" class="alert alert-warning small mb-0">
                                <i class="bi bi-geo-alt me-1"></i>
                                Izin lokasi diperlukan untuk melakukan presensi.
                            </div>
                            <button type="button" id="request-location-permission" class="btn btn-outline-secondary">
                                <i class="bi bi-crosshair me-1"></i> Aktifkan Lokasi
                            </button>
                            <form action="{{ route('admin.attendances.check-out') }}" method="POST"
                                class="location-attendance-form">
                                @csrf
                                <input type="hidden" name="latitude">
                                <input type="hidden" name="longitude">
                                <div class="mb-2">
                                    <input type="text" name="notes" class="form-control form-control-sm"
                                        placeholder="Catatan (opsional)">
                                </div>
                                <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('Konfirmasi presensi pulang?')">
                                    <i class="bi bi-box-arrow-right me-1"></i> Presensi Pulang
                                </button>
                            </form>
                        @else
                            <div class="alert alert-info mb-0 text-center">
                                <i class="bi bi-check-circle me-1"></i> Presensi hari ini sudah lengkap.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Riwayat Presensi --}}
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="bi bi-clock-history me-2"></i>Riwayat Presensi Terakhir</h6>
                </div>
                <div class="card-body p-0">
                    @if ($logs->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Masuk</th>
                                        <th>Pulang</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($logs as $log)
                                        <tr>
                                            <td>{{ $log->date->format('d/m/Y') }}</td>
                                            <td>{{ $log->check_in ?? '-' }}</td>
                                            <td>{{ $log->check_out ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-{{ $log->status->color() }}">
                                                    {{ $log->status->label() }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $logs->links() }}
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            Belum ada riwayat presensi.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <style>
        .attendance-view-map {
            height: 280px;
            min-height: 240px;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            overflow: hidden;
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.querySelectorAll('.location-attendance-form').forEach(function(form) {
            const status = document.getElementById('location-permission-status');
            const permissionButton = document.getElementById('request-location-permission');

            function requestLocation() {
                if (!navigator.geolocation) {
                    status.className = 'alert alert-danger small mb-0';
                    status.innerHTML =
                        '<i class="bi bi-exclamation-triangle me-1"></i> Browser ini tidak mendukung akses lokasi.';
                    return;
                }

                navigator.geolocation.getCurrentPosition(function(position) {
                    form.querySelector('[name="latitude"]').value = position.coords.latitude;
                    form.querySelector('[name="longitude"]').value = position.coords.longitude;
                    form.dataset.locationReady = 'true';
                    status.className = 'alert alert-success small mb-0';
                    status.innerHTML =
                        '<i class="bi bi-check-circle me-1"></i> Lokasi berhasil diaktifkan.';
                    permissionButton.classList.add('d-none');
                }, function(error) {
                    status.className = 'alert alert-danger small mb-0';
                    status.innerHTML = error.code === error.PERMISSION_DENIED ?
                        '<i class="bi bi-lock me-1"></i> Izin lokasi ditolak. Izinkan lokasi dari pengaturan browser lalu coba lagi.' :
                        '<i class="bi bi-exclamation-triangle me-1"></i> Lokasi tidak dapat diakses. Coba lagi.';
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                });
            }

            permissionButton.addEventListener('click', requestLocation);
            requestLocation();

            form.addEventListener('submit', function(event) {
                if (form.dataset.locationReady === 'true') {
                    return;
                }

                event.preventDefault();
                requestLocation();
            });
        });
    </script>
@endpush

@push('scripts')
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapElement = document.getElementById('attendance-view-map');
            const statusElement = document.getElementById('attendance-map-status');
            const locateButton = document.getElementById('show-my-location');

            if (!mapElement || typeof L === 'undefined') {
                return;
            }

            const latitude = parseFloat(mapElement.dataset.latitude);
            const longitude = parseFloat(mapElement.dataset.longitude);
            const radius = parseInt(mapElement.dataset.radius, 10) || 100;

            if (!Number.isFinite(latitude) || !Number.isFinite(longitude)) {
                statusElement.className = 'alert alert-warning small mt-2 mb-0';
                statusElement.innerHTML =
                    '<i class="bi bi-exclamation-triangle me-1"></i> Lokasi presensi belum dikonfigurasi oleh Super Admin.';
                locateButton.disabled = true;
                return;
            }

            const center = [latitude, longitude];
            const map = L.map(mapElement).setView(center, 16);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
            }).addTo(map);

            const officeMarker = L.marker(center).addTo(map).bindPopup('Lokasi presensi');
            const radiusCircle = L.circle(center, {
                radius: radius,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.18,
                weight: 2
            }).addTo(map);
            map.fitBounds(radiusCircle.getBounds(), {
                padding: [24, 24]
            });

            let currentMarker;

            function locateUser() {
                statusElement.className = 'small text-muted mt-2';
                statusElement.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Mencari posisi perangkat...';
                map.locate({
                    enableHighAccuracy: true,
                    maximumAge: 0,
                    timeout: 10000
                });
            }

            map.on('locationfound', function(event) {
                if (currentMarker) {
                    currentMarker.remove();
                }

                currentMarker = L.marker(event.latlng).addTo(map).bindPopup('Posisi perangkat Anda');
                statusElement.className = 'alert alert-success small mt-2 mb-0';
                statusElement.innerHTML =
                    '<i class="bi bi-check-circle me-1"></i> Posisi perangkat tampil. Pastikan marker berada di dalam lingkaran radius biru.';
            });

            map.on('locationerror', function() {
                statusElement.className = 'alert alert-danger small mt-2 mb-0';
                statusElement.innerHTML =
                    '<i class="bi bi-lock me-1"></i> Posisi perangkat tidak dapat diakses. Izinkan lokasi browser lalu coba lagi.';
            });

            locateButton.addEventListener('click', locateUser);
            locateUser();

            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        });
    </script>
@endpush
