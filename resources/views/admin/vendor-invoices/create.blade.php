@extends('layouts.app')

@section('title', 'Invoice Vendor Gabungan')
@section('page-title', 'Invoice Vendor Gabungan')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Invoice Vendor Gabungan</h1>
            <p class="text-muted mb-0">Gabungkan beberapa WO vendor dan laporan pekerjaan dalam satu PDF.</p>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.vendor-invoices.download') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-4 mb-3"><label for="vendor_id" class="form-label">Vendor</label><select
                            class="form-select @error('vendor_id') is-invalid @enderror" id="vendor_id" name="vendor_id"
                            required>
                            <option value="">Pilih Vendor</option>
                            @foreach ($vendors as $vendor)
                                <option value="{{ $vendor->id }}" @selected(old('vendor_id') == $vendor->id)>{{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('vendor_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3"><label for="from" class="form-label">Dari Tanggal
                            Pengerjaan</label><input type="text"
                            class="form-control datepicker @error('from') is-invalid @enderror" id="from"
                            name="from" value="{{ old('from', now()->startOfWeek()->format('Y-m-d')) }}" required>
                        @error('from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3"><label for="to" class="form-label">Sampai Tanggal
                            Pengerjaan</label><input type="text"
                            class="form-control datepicker @error('to') is-invalid @enderror" id="to" name="to"
                            value="{{ old('to', now()->endOfWeek()->format('Y-m-d')) }}" required>
                        @error('to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-file-earmark-pdf me-1"></i> Download Invoice
                    Gabungan</button>
                <a href="{{ route('admin.finance.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
