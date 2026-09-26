@extends('layouts.app')

@section('title', 'Edit Staff')
@section('page-title', 'Edit Data Staff')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.staff.update', $staff) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $staff->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $staff->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Nomor Telepon</label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $staff->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                        @foreach(App\Enums\UserRole::cases() as $role)
                            <option value="{{ $role->value }}" {{ old('role', $staff->role->value) == $role->value ? 'selected' : '' }}>
                                {{ $role->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('role')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-12 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $staff->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">
                            Status Aktif
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <hr>
                    <p class="text-muted small">Kosongkan password jika tidak ingin mengganti.</p>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password" class="form-label">Password Baru</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password">
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                </div>

                <div class="col-12 mt-3 mb-2">
                    <h6 class="fw-bold border-bottom pb-2">Jadwal Presensi Individual (Opsional)</h6>
                    <small class="text-muted d-block mb-3">Jika diisi, jadwal ini akan mengesampingkan jadwal Role dan Global.</small>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="work_start_time" class="form-label">Jam Masuk</label>
                    <input type="time" class="form-control @error('work_start_time') is-invalid @enderror" id="work_start_time" name="work_start_time" value="{{ old('work_start_time', $staff->work_start_time ? \Carbon\Carbon::parse($staff->work_start_time)->format('H:i') : '') }}">
                    @error('work_start_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label for="work_end_time" class="form-label">Jam Pulang</label>
                    <input type="time" class="form-control @error('work_end_time') is-invalid @enderror" id="work_end_time" name="work_end_time" value="{{ old('work_end_time', $staff->work_end_time ? \Carbon\Carbon::parse($staff->work_end_time)->format('H:i') : '') }}">
                    @error('work_end_time')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.staff.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
