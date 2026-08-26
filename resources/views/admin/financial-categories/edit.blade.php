@extends('layouts.app')

@section('title', 'Edit Kategori ' . ucfirst($categoryLabel))
@section('page-title', 'Edit Kategori ' . ucfirst($categoryLabel))

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route("admin.{$routePrefix}.update", $financialCategory) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Kategori {{ ucfirst($categoryLabel) }}</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                        name="name" value="{{ old('name', $financialCategory->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description"
                        rows="4">{{ old('description', $financialCategory->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                        {{ old('is_active', $financialCategory->is_active) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Status Aktif</label>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route("admin.{$routePrefix}.index") }}" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
@endsection
