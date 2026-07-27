@extends('layouts.app')

@section('title', 'Kategori Layanan')
@section('page-title', 'Kategori Layanan')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Kategori Layanan</h1>
        <p class="text-muted mb-0">Atur kategori jasa agar work order lebih konsisten dan mudah dicari.</p>
    </div>
    <a href="{{ route('admin.service-categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
    </a>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Daftar Kategori</h5>
            <p class="text-muted small mb-0">Pagination server-side untuk menjaga performa database.</p>
        </div>
        <span class="badge text-bg-light border">Total: {{ $categories->total() }}</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama Kategori</th>
                        <th>Deskripsi</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $category)
                    <tr>
                        <td>{{ $category->name }}</td>
                        <td>{{ Str::limit($category->description, 100) }}</td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.service-categories.edit', $category) }}" class="btn btn-sm btn-warning btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.service-categories.destroy', $category) }}" method="POST" onsubmit="return confirmDelete('Yakin ingin menghapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger btn-action" title="Hapus">
                                        <i class="bi bi-trash"></i>
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
                Menampilkan {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} dari {{ $categories->total() }} data
            </div>
            {{ $categories->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
