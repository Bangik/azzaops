<div class="row">
    <div class="col-md-6 mb-3"><label for="name" class="form-label">Nama Vendor</label><input
            class="form-control @error('name') is-invalid @enderror" id="name" name="name"
            value="{{ old('name', $vendor?->name) }}" required>
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3"><label for="phone" class="form-label">Telepon</label><input
            class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone"
            value="{{ old('phone', $vendor?->phone) }}">
        @error('phone')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6 mb-3"><label for="email" class="form-label">Email</label><input type="email"
            class="form-control @error('email') is-invalid @enderror" id="email" name="email"
            value="{{ old('email', $vendor?->email) }}">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-12 mb-3"><label for="address" class="form-label">Alamat</label>
        <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $vendor?->address) }}</textarea>
    </div>
    <div class="col-md-12 mb-3"><label for="notes" class="form-label">Catatan</label>
        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $vendor?->notes) }}</textarea>
    </div>
    <div class="col-md-12 mb-3 form-check ms-2"><input type="checkbox" class="form-check-input" id="is_active"
            name="is_active" value="1" {{ old('is_active', $vendor?->is_active ?? true) ? 'checked' : '' }}><label
            class="form-check-label" for="is_active">Vendor Aktif</label></div>
</div>
<button type="submit" class="btn btn-primary">Simpan</button> <a href="{{ route('admin.vendors.index') }}"
    class="btn btn-secondary">Batal</a>
