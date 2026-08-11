@extends('layouts.app')

@section('title', 'Tipe Pekerjaan')
@section('page-title', 'Tipe Pekerjaan')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Tipe Pekerjaan</h1>
        <p class="text-muted mb-0">Kelola tipe pekerjaan untuk Work Order secara dinamis.</p>
    </div>
    <div>
        <a href="{{ route('admin.work-order-types.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Tipe Pekerjaan
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kode</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                    <tr>
                        <td><strong>{{ $type->name }}</strong></td>
                        <td><code>{{ $type->code }}</code></td>
                        <td>{{ $type->description ?: '-' }}</td>
                        <td>
                            @if($type->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.work-order-types.edit', $type) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.work-order-types.destroy', $type) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tipe pekerjaan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data tipe pekerjaan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
