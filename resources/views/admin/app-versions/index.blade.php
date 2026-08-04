@extends('layouts.app')

@section('title', 'Manajemen Versi Aplikasi')
@section('page-title', 'Manajemen Versi Aplikasi')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Versi Aplikasi</h1>
        <p class="text-muted mb-0">Kelola rilis APK dan pembaruan sistem in-app untuk aplikasi Android staff.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Upload Versi APK Baru</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.app-versions.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="apk_file" class="form-label">File APK (.apk) <span class="text-danger">*</span></label>
                        <input type="file" name="apk_file" id="apk_file" class="form-control @error('apk_file') is-invalid @enderror" accept=".apk" required>
                        @error('apk_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="version_code" class="form-label">Version Code <span class="text-danger">*</span></label>
                        <input type="number" name="version_code" id="version_code" class="form-control @error('version_code') is-invalid @enderror" value="{{ old('version_code') }}" placeholder="Contoh: 2" required>
                        @error('version_code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="version_name" class="form-label">Version Name <span class="text-danger">*</span></label>
                        <input type="text" name="version_name" id="version_name" class="form-control @error('version_name') is-invalid @enderror" value="{{ old('version_name') }}" placeholder="Contoh: 1.0.1" required>
                        @error('version_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="release_notes" class="form-label">Catatan Rilis (Release Notes)</label>
                        <textarea name="release_notes" id="release_notes" rows="4" class="form-control @error('release_notes') is-invalid @enderror" placeholder="Tulis catatan perubahan versi ini...">{{ old('release_notes') }}</textarea>
                        @error('release_notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-cloud-arrow-up me-1"></i> Upload & Rilis
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Riwayat Rilis APK</h5>
                <span class="badge text-bg-light border">Total: {{ $versions->total() }}</span>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Version Code</th>
                                <th>Version Name</th>
                                <th>Release Notes</th>
                                <th>File Size</th>
                                <th>Tanggal Upload</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($versions as $version)
                            <tr>
                                <td><span class="badge bg-primary">Code: {{ $version->version_code }}</span></td>
                                <td><strong>v{{ $version->version_name }}</strong></td>
                                <td>
                                    <span class="text-muted small d-inline-block text-truncate" style="max-width: 250px;" title="{{ $version->release_notes }}">
                                        {{ $version->release_notes ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $filePath = 'public/' . $version->apk_file_path;
                                        $size = Illuminate\Support\Facades\Storage::exists($filePath) 
                                            ? round(Illuminate\Support\Facades\Storage::size($filePath) / 1024 / 1024, 2) . ' MB'
                                            : '-';
                                    @endphp
                                    {{ $size }}
                                </td>
                                <td>{{ $version->created_at->format('d M Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ $version->download_url }}" class="btn btn-sm btn-outline-success" title="Download APK">
                                            <i class="bi bi-download"></i>
                                        </a>
                                        <form action="{{ route('admin.app-versions.destroy', $version) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus versi ini? File APK juga akan dihapus dari storage.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada versi APK yang dirilis.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="pagination-footer">
                    <div class="pagination-info">
                        Menampilkan {{ $versions->firstItem() ?? 0 }} - {{ $versions->lastItem() ?? 0 }} dari {{ $versions->total() }} data
                    </div>
                    {{ $versions->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
