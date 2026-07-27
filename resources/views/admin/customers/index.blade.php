@extends('layouts.app')

@section('title', 'Customer')
@section('page-title', 'Manajemen Customer')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Manajemen Customer</h1>
        <p class="text-muted mb-0">Kelola pelanggan perorangan dan perusahaan dengan tampilan yang lebih rapi.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.index', ['type' => 'customers']) }}" class="btn btn-outline-primary">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Laporan Customer
        </a>
        <a href="{{ route('admin.customers.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Customer
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-1">Daftar Customer</h5>
            <p class="text-muted small mb-0">Data dimuat per halaman agar query tetap ringan.</p>
        </div>
        <span class="badge text-bg-light border">Total: {{ $customers->total() }}</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Perusahaan</th>
                        <th>Telepon</th>
                        <th>Kota</th>
                        <th>Market</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                    <tr>
                        <td>
                            <a href="{{ route('admin.customers.show', $customer) }}" class="text-decoration-none fw-semibold">
                                {{ $customer->name }}
                            </a>
                        </td>
                        <td>
                            <span class="badge bg-{{ $customer->type->value === 'individual' ? 'info' : 'primary' }}">
                                {{ $customer->type->label() }}
                            </span>
                        </td>
                        <td>{{ $customer->company_name ?? '-' }}</td>
                        <td>{{ $customer->phone }}</td>
                        <td>{{ $customer->city ?? '-' }}</td>
                        <td>{{ $customer->market ?? '-' }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.customers.show', $customer) }}" class="btn btn-sm btn-info text-white btn-action" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('admin.customers.edit', $customer) }}" class="btn btn-sm btn-warning btn-action" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST" onsubmit="return confirmDelete('Yakin ingin menghapus customer ini?')">
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
                Menampilkan {{ $customers->firstItem() ?? 0 }} - {{ $customers->lastItem() ?? 0 }} dari {{ $customers->total() }} data
            </div>
            {{ $customers->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
