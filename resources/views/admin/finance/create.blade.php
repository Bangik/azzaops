@extends('layouts.app')

@section('title', 'Catat Pengeluaran')
@section('page-title', 'Catat Pengeluaran')

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.expenses.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="category_id" class="form-label">Kategori Pengeluaran</label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id"
                            name="category_id" required>
                            <option value="">Pilih Kategori</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="work_order_id" class="form-label">Link ke Work Order (Opsional)</label>
                        <select class="form-select @error('work_order_id') is-invalid @enderror" id="work_order_id"
                            name="work_order_id">
                            <option value="">Tidak Terkait Work Order</option>
                            @foreach ($workOrders as $wo)
                                <option value="{{ $wo->id }}" {{ old('work_order_id') == $wo->id ? 'selected' : '' }}>
                                    {{ $wo->wo_number }} - {{ $wo->title }}
                                </option>
                            @endforeach
                        </select>
                        @error('work_order_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="description" class="form-label">Deskripsi Pengeluaran</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror"
                            id="description" name="description" value="{{ old('description') }}"
                            placeholder="Contoh: Beli Pipa AC Daikin 5m" required>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="amount" class="form-label">Nominal (Rp)</label>
                        <input type="number" class="form-control @error('amount') is-invalid @enderror" id="amount"
                            name="amount" value="{{ old('amount') }}" required min="1">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="pic" class="form-label">PIC Pengeluaran (Opsional)</label>
                        <input type="text" class="form-control @error('pic') is-invalid @enderror" id="pic"
                            name="pic" value="{{ old('pic') }}" placeholder="Nama PIC atau pihak terkait">
                        @error('pic')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="expense_date" class="form-label">Tanggal Pengeluaran</label>
                        <input type="text" class="form-control datepicker @error('expense_date') is-invalid @enderror"
                            id="expense_date" name="expense_date" value="{{ old('expense_date', now()->format('Y-m-d')) }}"
                            required>
                        @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="receipt_photo" class="form-label">Foto Struk / Nota (Opsional)</label>
                        <input type="file" class="form-control @error('receipt_photo') is-invalid @enderror"
                            id="receipt_photo" name="receipt_photo" accept="image/*">
                        @error('receipt_photo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12 mb-3">
                        <label for="notes" class="form-label">Catatan Tambahan</label>
                        <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Simpan Pengeluaran</button>
                    <a href="{{ route('admin.finance.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
