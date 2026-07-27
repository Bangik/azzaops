@extends('layouts.app')

@section('title', 'Staff')
@section('page-title', 'Manajemen Staff')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Staff</h1>
        <p class="text-muted mb-0">Kelola akun internal dan hak akses pengguna.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.index', ['type' => 'staff']) }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Laporan Kinerja
        </a>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Staff
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Daftar Staff</h5>
            <p class="text-muted small mb-0">Menampilkan data staff secara bertahap untuk menjaga performa.</p>
        </div>
        <span class="badge text-bg-light border">Total: {{ $staff->total() }}</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($staff as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ match($user->role->value) {
                                'super_admin' => 'danger',
                                'admin' => 'warning',
                                'kepala_teknisi' => 'info',
                                'teknisi' => 'success',
                                default => 'secondary'
                            } }}">
                                {{ $user->role->label() }}
                            </span>
                        </td>
                        <td>
                            @if($user->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.staff.edit', $user) }}" class="btn btn-sm btn-warning btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.staff.destroy', $user) }}" method="POST" onsubmit="return confirmDelete('Yakin ingin menonaktifkan staff ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" title="Nonaktifkan">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="pagination-footer">
            <div class="pagination-info">
                Menampilkan {{ $staff->firstItem() ?? 0 }} - {{ $staff->lastItem() ?? 0 }} dari {{ $staff->total() }} data
            </div>
            {{ $staff->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
