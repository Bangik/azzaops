@extends('layouts.app')

@section('title', 'Pengaturan Sistem & Dokumen')
@section('page-title', 'Pengaturan')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Pengaturan Sistem & Dokumen</h1>
            <p class="text-muted mb-0">Kelola identitas perusahaan, logo, header, dan detail metode pembayaran untuk invoice & tanda terima.</p>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Terjadi kesalahan validasi:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row g-4">
            {{-- Tab 1: Logo & Identitas Header Perusahaan --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <i class="bi bi-building fs-5 text-primary me-2"></i>
                        <h5 class="card-title mb-0 fw-bold">Identitas & Header Dokumen</h5>
                    </div>
                    <div class="card-body">
                        {{-- Logo Perusahaan --}}
                        <div class="mb-4 p-3 bg-light rounded border">
                            <label class="form-label fw-bold d-block">Logo Perusahaan</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-white border rounded p-2 text-center d-flex align-items-center justify-content-center" style="width: 140px; height: 70px;">
                                    @if (!empty($settings['company_logo']) && file_exists(public_path($settings['company_logo'])))
                                        <img src="{{ asset($settings['company_logo']) }}" alt="Logo" class="img-fluid" style="max-height: 54px; max-width: 120px; object-fit: contain;">
                                    @else
                                        <span class="text-muted small"><i class="bi bi-image fs-4 d-block"></i>Belum ada logo</span>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <input type="file" name="company_logo" id="company_logo" class="form-control form-control-sm @error('company_logo') is-invalid @enderror" accept="image/png, image/jpeg, image/jpg, image/webp, image/svg+xml">
                                    <div class="form-text small">Format: PNG, JPG, WEBP, SVG (Maks. 5 MB).</div>
                                    @if (!empty($settings['company_logo']))
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_logo" value="1" id="remove_logo">
                                            <label class="form-check-label text-danger small" for="remove_logo">
                                                Hapus logo saat ini
                                            </label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Nama Perusahaan --}}
                        <div class="mb-3">
                            <label for="company_name" class="form-label fw-bold">Nama Perusahaan <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" id="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $settings['company_name'] ?? '') }}" required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tagline --}}
                        <div class="mb-3">
                            <label for="company_tagline" class="form-label fw-bold">Tagline / Sub Judul Header</label>
                            <input type="text" name="company_tagline" id="company_tagline" class="form-control" value="{{ old('company_tagline', $settings['company_tagline'] ?? '') }}" placeholder="Contoh: Solusi AC & Elektronik Terpercaya">
                            <div class="form-text small">Ditampilkan tepat di bawah nama perusahaan pada header invoice & tanda terima.</div>
                        </div>

                        {{-- Alamat --}}
                        <div class="mb-3">
                            <label for="company_address" class="form-label fw-bold">Alamat Perusahaan</label>
                            <textarea name="company_address" id="company_address" rows="3" class="form-control" placeholder="Alamat kantor / operasional">{{ old('company_address', $settings['company_address'] ?? '') }}</textarea>
                        </div>

                        {{-- Kontak --}}
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="company_phone" class="form-label fw-bold">No. Telepon</label>
                                <input type="text" name="company_phone" id="company_phone" class="form-control" value="{{ old('company_phone', $settings['company_phone'] ?? '') }}" placeholder="021-xxxxxxx">
                            </div>
                            <div class="col-md-6">
                                <label for="company_wa" class="form-label fw-bold">No. WhatsApp</label>
                                <input type="text" name="company_wa" id="company_wa" class="form-control" value="{{ old('company_wa', $settings['company_wa'] ?? '') }}" placeholder="08xxxxxxxxxx">
                            </div>
                            <div class="col-12">
                                <label for="company_email" class="form-label fw-bold">Email Perusahaan</label>
                                <input type="email" name="company_email" id="company_email" class="form-control @error('company_email') is-invalid @enderror" value="{{ old('company_email', $settings['company_email'] ?? '') }}" placeholder="email@perusahaan.com">
                                @error('company_email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 2: Detail Metode Pembayaran --}}
            <div class="col-lg-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <i class="bi bi-credit-card fs-5 text-success me-2"></i>
                        <h5 class="card-title mb-0 fw-bold">Detail Metode Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Informasi rekening dan tata cara pembayaran yang tercantum pada dokumen Invoice dan Tanda Terima.</p>

                        <div class="row g-3">
                            <div class="col-md-8">
                                <label for="payment_bank_name" class="form-label fw-bold">Nama Bank</label>
                                <input type="text" name="payment_bank_name" id="payment_bank_name" class="form-control" value="{{ old('payment_bank_name', $settings['payment_bank_name'] ?? '') }}" placeholder="Contoh: Bank Central Asia (BCA)">
                            </div>
                            <div class="col-md-4">
                                <label for="payment_bank_code" class="form-label fw-bold">Kode Bank</label>
                                <input type="text" name="payment_bank_code" id="payment_bank_code" class="form-control" value="{{ old('payment_bank_code', $settings['payment_bank_code'] ?? '') }}" placeholder="Contoh: 014">
                            </div>
                            <div class="col-md-6">
                                <label for="payment_account_number" class="form-label fw-bold">Nomor Rekening</label>
                                <input type="text" name="payment_account_number" id="payment_account_number" class="form-control" value="{{ old('payment_account_number', $settings['payment_account_number'] ?? '') }}" placeholder="Contoh: 1234567890">
                            </div>
                            <div class="col-md-6">
                                <label for="payment_account_holder" class="form-label fw-bold">Atas Nama Rekening (A.N.)</label>
                                <input type="text" name="payment_account_holder" id="payment_account_holder" class="form-control" value="{{ old('payment_account_holder', $settings['payment_account_holder'] ?? '') }}" placeholder="Nama pemilik rekening">
                            </div>
                            <div class="col-12">
                                <label for="payment_instructions" class="form-label fw-bold">Catatan / Instruksi Pembayaran</label>
                                <textarea name="payment_instructions" id="payment_instructions" rows="3" class="form-control" placeholder="Contoh: Mohon sertakan nomor invoice pada berita transfer dan kirimkan bukti transfer ke WhatsApp operasional.">{{ old('payment_instructions', $settings['payment_instructions'] ?? '') }}</textarea>
                                <div class="form-text small">Teks panduan tambahan yang muncul di bagian instruksi pembayaran invoice.</div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-pen me-2 text-secondary"></i>Penandatangan Dokumen
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="invoice_signer_name" class="form-label fw-bold">Nama Penandatangan</label>
                                <input type="text" name="invoice_signer_name" id="invoice_signer_name" class="form-control" value="{{ old('invoice_signer_name', $settings['invoice_signer_name'] ?? '') }}" placeholder="Contoh: Brandon Erman">
                            </div>
                            <div class="col-md-6">
                                <label for="invoice_signer_position" class="form-label fw-bold">Jabatan</label>
                                <input type="text" name="invoice_signer_position" id="invoice_signer_position" class="form-control" value="{{ old('invoice_signer_position', $settings['invoice_signer_position'] ?? '') }}" placeholder="Contoh: Kepala Operasional">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tab 3: Template Dokumen & Catatan --}}
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
                        <i class="bi bi-file-earmark-text fs-5 text-warning me-2"></i>
                        <h5 class="card-title mb-0 fw-bold">Konfigurasi Nomor & Teks Dokumen</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <label for="invoice_prefix" class="form-label fw-bold">Prefix Invoice</label>
                                <input type="text" name="invoice_prefix" id="invoice_prefix" class="form-control" value="{{ old('invoice_prefix', $settings['invoice_prefix'] ?? 'INV') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="wo_prefix" class="form-label fw-bold">Prefix Work Order</label>
                                <input type="text" name="wo_prefix" id="wo_prefix" class="form-control" value="{{ old('wo_prefix', $settings['wo_prefix'] ?? 'WO') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="rab_prefix" class="form-label fw-bold">Prefix RAB</label>
                                <input type="text" name="rab_prefix" id="rab_prefix" class="form-control" value="{{ old('rab_prefix', $settings['rab_prefix'] ?? 'RAB') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="tax_default" class="form-label fw-bold">Default Pajak PPN (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.1" min="0" max="100" name="tax_default" id="tax_default" class="form-control" value="{{ old('tax_default', $settings['tax_default'] ?? '0') }}">
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="invoice_footer" class="form-label fw-bold">Teks Ucapan / Footer Dokumen</label>
                                <input type="text" name="invoice_footer" id="invoice_footer" class="form-control" value="{{ old('invoice_footer', $settings['invoice_footer'] ?? '') }}" placeholder="Contoh: TERIMA KASIH ATAS KEPERCAYAAN ANDA">
                            </div>
                            <div class="col-md-6">
                                <label for="invoice_terms" class="form-label fw-bold">Syarat & Ketentuan (Terms & Conditions)</label>
                                <textarea name="invoice_terms" id="invoice_terms" rows="2" class="form-control" placeholder="Contoh: Pembayaran jatuh tempo sesuai tanggal tertera. Garansi pengerjaan 30 hari.">{{ old('invoice_terms', $settings['invoice_terms'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-light py-3 text-end">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-save me-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
