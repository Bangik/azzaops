@extends('layouts.app')

@section('title', 'Akun Keuangan')
@section('page-title', 'Akun Keuangan')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Akun Keuangan</h1>
        <p class="text-muted mb-0">Kelola akun keuangan seperti Giro, Rekening, Cash, dll.</p>
    </div>
    <div>
        <a href="{{ route('admin.financial-accounts.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Akun Keuangan
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
                    @forelse($accounts as $account)
                    <tr>
                        <td><strong>{{ $account->name }}</strong></td>
                        <td><code>{{ $account->code }}</code></td>
                        <td>{{ $account->description ?: '-' }}</td>
                        <td>
                            @if($account->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.financial-accounts.edit', $account) }}" class="btn btn-sm btn-warning">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <form action="{{ route('admin.financial-accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun keuangan ini?')">
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
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada data akun keuangan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
