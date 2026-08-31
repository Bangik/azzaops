@extends('layouts.app')

@section('title', 'Vendor')
@section('page-title', 'Vendor')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Vendor</h1>
            <p class="text-muted mb-0">Kelola vendor dan kelompokkan work order untuk penagihan gabungan.</p>
        </div>
        <a href="{{ route('admin.vendors.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> Tambah
            Vendor</a>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kontak</th>
                            <th>Jumlah WO</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vendors as $vendor)
                            <tr>
                                <td class="fw-semibold">{{ $vendor->name }}</td>
                                <td>{{ $vendor->phone ?: '-' }}<br><small
                                        class="text-muted">{{ $vendor->email ?: '-' }}</small></td>
                                <td>{{ $vendor->work_orders_count }}</td>
                                <td><span
                                        class="badge {{ $vendor->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $vendor->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1"><a href="{{ route('admin.vendors.edit', $vendor) }}"
                                            class="btn btn-sm btn-warning btn-action"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.vendors.destroy', $vendor) }}" method="POST"
                                            onsubmit="return confirmDelete('Yakin ingin menghapus vendor ini?')">@csrf
                                            @method('DELETE')<button class="btn btn-sm btn-danger btn-action"><i
                                                    class="bi bi-trash"></i></button></form>
                                    </div>
                                </td>
                            </tr>
                        @empty <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada vendor</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>{{ $vendors->links() }}
        </div>
    </div>
@endsection
