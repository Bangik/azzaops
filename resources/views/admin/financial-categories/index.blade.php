@extends('layouts.app')

@section('title', 'Kategori Pengeluaran')
@section('page-title', 'Kategori Pengeluaran')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Kategori Pengeluaran</h1>
            <p class="text-muted mb-0">Kelola kategori yang tersedia saat mencatat pengeluaran operasional.</p>
        </div>
        <a href="{{ route('admin.financial-categories.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kategori
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Daftar Kategori</h5>
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
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td>{{ Str::limit($category->description, 100) }}</td>
                                <td>
                                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $category->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('admin.financial-categories.edit', $category) }}"
                                            class="btn btn-sm btn-warning btn-action" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="{{ route('admin.financial-categories.destroy', $category) }}"
                                            method="POST"
                                            onsubmit="return confirmDelete('Yakin ingin menghapus kategori ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger btn-action" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">Belum ada kategori pengeluaran</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pagination-footer">
                <div class="pagination-info">
                    Menampilkan {{ $categories->firstItem() ?? 0 }} - {{ $categories->lastItem() ?? 0 }} dari
                    {{ $categories->total() }} data
                </div>
                {{ $categories->onEachSide(1)->links() }}
            </div>
        </div>
    </div>
@endsection
