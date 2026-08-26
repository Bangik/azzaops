@extends('layouts.app')

@section('title', 'Catat Pemasukan')
@section('page-title', 'Catat Pemasukan')

@section('content')
    <div class="card">
        <div class="card-body">
            <p class="text-muted">Gunakan formulir ini untuk pemasukan yang tidak berasal dari invoice atau work order.</p>
            <form action="{{ route('admin.incomes.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Kategori Pemasukan</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                            name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="transaction_date" class="form-label">Tanggal Pemasukan</label>
                        <input type="text"
                            class="form-control datepicker @error('transaction_date') is-invalid @enderror"
                            id="transaction_date" name="transaction_date"
                            value="{{ old('transaction_date', now()->format('Y-m-d')) }}" required>
                        @error('transaction_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label for="description" class="form-label">Deskripsi Pemasukan</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" value="{{ old('description') }}"
                            placeholder="Contoh: Pendapatan jasa konsultasi" required>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label for="amount" class="form-label">Nominal (Rp)</label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount"
                            name="amount" value="{{ old('amount') }}" min="1" required>
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="reference_number" class="form-label">Nomor Referensi (Opsional)</label>
                        <input type="text" class="form-control @error('reference_number') is-invalid @enderror"
                            id="reference_number" name="reference_number" value="{{ old('reference_number') }}"
                            placeholder="Contoh: Nomor bukti transfer">
                        @error('reference_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Pemasukan</button>
                <a href="{{ route('admin.finance.index') }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
