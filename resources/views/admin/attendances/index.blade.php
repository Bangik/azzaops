@extends('layouts.app')

@section('title', 'Log Presensi')
@section('page-title', 'Log Presensi Staff')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Log Presensi Staff</h1>
            <p class="text-muted mb-0">Rekap presensi seluruh staff berdasarkan rentang waktu.</p>
        </div>
        @if ($attendances->count() > 0)
            <div class="d-flex gap-2">
                <a href="{{ route('admin.attendances.export', array_merge(request()->all(), ['format' => 'xlsx'])) }}"
                    class="btn btn-success">
                    <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
                </a>
                <a href="{{ route('admin.attendances.export', array_merge(request()->all(), ['format' => 'csv'])) }}"
                    class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet me-1"></i> Ekspor CSV
                </a>
            </div>
        @endif
    </div>

    {{-- Setting Jadwal Kerja --}}
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="bi bi-clock me-2"></i>Jadwal Kerja</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.attendances.update-schedule') }}" method="POST" class="row g-3 align-items-end">
                @csrf
                @method('PUT')
                <div class="col-md-2">
                    <label class="form-label">Jam Masuk</label>
                    <input type="time" name="work_start_time" class="form-control"
                        value="{{ $schedule['work_start_time'] }}" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Jam Pulang</label>
                    <input type="time" name="work_end_time" class="form-control" value="{{ $schedule['work_end_time'] }}"
                        required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Radius Presensi (meter)</label>
                    <input type="number" name="attendance_radius_meters" class="form-control"
                        value="{{ $schedule['attendance_radius_meters'] }}" min="1" max="100000" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Latitude</label>
                    <input type="number" name="attendance_latitude" class="form-control" step="0.0000001"
                        value="{{ $schedule['attendance_latitude'] }}" min="-90" max="90" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Longitude</label>
                    <input type="number" name="attendance_longitude" class="form-control" step="0.0000001"
                        value="{{ $schedule['attendance_longitude'] }}" min="-180" max="180" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Simpan Jadwal
                    </button>
                </div>
            </form>

            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <h6 class="mb-1"><i class="bi bi-map me-2"></i>Lokasi dan Radius Presensi</h6>
                        <small class="text-muted">Klik peta atau geser marker untuk menentukan titik lokasi
                            presensi.</small>
                    </div>
                    <span class="badge text-bg-light border">OpenStreetMap</span>
                </div>
                <div id="attendance-location-map" class="attendance-location-map"
                    data-latitude="{{ $schedule['attendance_latitude'] }}"
                    data-longitude="{{ $schedule['attendance_longitude'] }}"
                    data-radius="{{ $schedule['attendance_radius_meters'] }}"></div>
                <div class="small text-muted mt-2">
                    <i class="bi bi-info-circle me-1"></i> Lingkaran menunjukkan area tempat staff boleh melakukan presensi.
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.attendances.index') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Dari Tanggal</label>
                    <input type="date" name="from" class="form-control" value="{{ $from }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Sampai Tanggal</label>
                    <input type="date" name="to" class="form-control" value="{{ $to }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Staff</label>
                    <select name="user_id" class="form-select">
                        <option value="">Semua Staff</option>
                        @foreach ($staffList as $staff)
                            <option value="{{ $staff->id }}" {{ $userId == $staff->id ? 'selected' : '' }}>
                                {{ $staff->name }} ({{ $staff->role->label() }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua</option>
                        @foreach (\App\Enums\AttendanceStatus::cases() as $s)
                            <option value="{{ $s->value }}" {{ $status == $s->value ? 'selected' : '' }}>
                                {{ $s->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Data Table --}}
    <div class="card">
        <div class="card-body">
            @if ($attendances->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="attendance-table">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Staff</th>
                                <th>Role</th>
                                <th>Jam Masuk</th>
                                <th>Jam Pulang</th>
                                <th>Status</th>
                                <th>Durasi</th>
                                <th>Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendances as $a)
                                <tr>
                                    <td>{{ $a->date->format('d/m/Y') }}</td>
                                    <td class="fw-semibold">{{ $a->user->name }}</td>
                                    <td>{{ $a->user->role->label() }}</td>
                                    <td>{{ $a->check_in ?? '-' }}</td>
                                    <td>{{ $a->check_out ?? '-' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $a->status->color() }}">
                                            {{ $a->status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $a->work_duration ?? '-' }}</td>
                                    <td>{{ $a->notes ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $attendances->links() }}
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-calendar-x fs-1 d-block mb-2"></i>
                    Tidak ada data presensi untuk filter yang dipilih.
                </div>
            @endif
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
    <style>
        .attendance-location-map {
            height: 360px;
            min-height: 280px;
            border-radius: .375rem;
            overflow: hidden;
            border: 1px solid #dee2e6;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mapElement = document.getElementById('attendance-location-map');
            if (!mapElement || typeof L === 'undefined') {
                return;
            }

            const form = document.querySelector('form[action="{{ route('admin.attendances.update-schedule') }}"]');
            const latitudeInput = form.querySelector('[name="attendance_latitude"]');
            const longitudeInput = form.querySelector('[name="attendance_longitude"]');
            const radiusInput = form.querySelector('[name="attendance_radius_meters"]');
            const defaultCenter = [-6.2000000, 106.8166667];
            const configuredLatitude = parseFloat(mapElement.dataset.latitude);
            const configuredLongitude = parseFloat(mapElement.dataset.longitude);
            const configuredRadius = parseInt(mapElement.dataset.radius, 10) || 100;
            const hasConfiguredLocation = Number.isFinite(configuredLatitude) && Number.isFinite(
                configuredLongitude);
            const center = hasConfiguredLocation ? [configuredLatitude, configuredLongitude] : defaultCenter;

            const map = L.map(mapElement).setView(center, hasConfiguredLocation ? 16 : 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>'
            }).addTo(map);

            let marker = hasConfiguredLocation ? L.marker(center, {
                draggable: true
            }).addTo(map) : null;
            let circle = hasConfiguredLocation ? L.circle(center, {
                radius: configuredRadius,
                color: '#0d6efd',
                fillColor: '#0d6efd',
                fillOpacity: 0.18,
                weight: 2
            }).addTo(map) : null;

            function setLocation(latitude, longitude) {
                latitudeInput.value = latitude.toFixed(7);
                longitudeInput.value = longitude.toFixed(7);

                if (!marker) {
                    marker = L.marker([latitude, longitude], {
                        draggable: true
                    }).addTo(map);
                    marker.on('dragend', function(event) {
                        const position = event.target.getLatLng();
                        setLocation(position.lat, position.lng);
                    });
                } else {
                    marker.setLatLng([latitude, longitude]);
                }

                if (!circle) {
                    circle = L.circle([latitude, longitude], {
                        radius: parseInt(radiusInput.value, 10) || 100,
                        color: '#0d6efd',
                        fillColor: '#0d6efd',
                        fillOpacity: 0.18,
                        weight: 2
                    }).addTo(map);
                } else {
                    circle.setLatLng([latitude, longitude]);
                }

                map.panTo([latitude, longitude]);
            }

            if (marker) {
                marker.on('dragend', function(event) {
                    const position = event.target.getLatLng();
                    setLocation(position.lat, position.lng);
                });
            }

            map.on('click', function(event) {
                setLocation(event.latlng.lat, event.latlng.lng);
            });

            radiusInput.addEventListener('input', function() {
                if (circle) {
                    circle.setRadius(parseInt(radiusInput.value, 10) || 0);
                }
            });

            setTimeout(function() {
                map.invalidateSize();
            }, 100);
        });
    </script>
@endpush
