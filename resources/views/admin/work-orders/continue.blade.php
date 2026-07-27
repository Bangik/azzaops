@extends('layouts.app')

@section('title', 'Lanjutkan Work Order')
@section('page-title', 'Lanjutkan Work Order Baru')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Lanjutkan Pengerjaan</h1>
        <p class="text-muted mb-0">Membuat perintah kerja lanjutan dari pengecekan {{ $workOrder->wo_number }} untuk {{ $workOrder->customer->name }}</p>
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm d-flex align-items-center mb-4">
    <i class="bi bi-info-circle-fill fs-4 me-3"></i>
    <div>
        <strong>Informasi Bisnis:</strong> Melanjutkan pekerjaan ini akan secara otomatis mengubah invoice pengecekan sebelumnya ({{ $workOrder->invoice ? $workOrder->invoice->invoice_number : '-' }}) menjadi <strong>Rp 0 (Gratis)</strong> dan menyelesaikan status work order pengecekan ini.
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.work-orders.store-continue', $workOrder) }}" method="POST">
            @csrf
            
            <h5 class="card-title mb-3 border-bottom pb-2">Informasi Pekerjaan Lanjutan</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Customer (Terkunci)</label>
                    <input type="text" class="form-control" value="{{ $workOrder->customer->name }}" disabled>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="service_category_id" class="form-label">Kategori Layanan</label>
                    <select class="form-select @error('service_category_id') is-invalid @enderror" id="service_category_id" name="service_category_id" required>
                        <option value="">Pilih Kategori</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('service_category_id', $workOrder->service_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('service_category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="type" class="form-label">Tipe Pekerjaan Lanjutan</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="service" {{ old('type') == 'service' ? 'selected' : '' }}>Servis / Perbaikan</option>
                        <option value="installation" {{ old('type') == 'installation' ? 'selected' : '' }}>Instalasi Baru</option>
                        <option value="maintenance" {{ old('type') == 'maintenance' ? 'selected' : '' }}>Perawatan Berkala</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="scheduled_date" class="form-label">Tanggal Rencana</label>
                    <input type="text" class="form-control datepicker @error('scheduled_date') is-invalid @enderror" id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date', now()->format('Y-m-d')) }}">
                    @error('scheduled_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="priority" class="form-label">Prioritas</label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                        @foreach(App\Enums\WorkOrderPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" {{ old('priority', 'normal') == $priority->value ? 'selected' : '' }}>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label for="title" class="form-label">Judul Singkat Pekerjaan Lanjutan</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', 'Perbaikan lanjutan: ' . $workOrder->title) }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label for="location" class="form-label">Alamat Lokasi (Sama dengan WO induk)</label>
                    <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="2" required>{{ old('location', $workOrder->location) }}</textarea>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label">Deskripsi & Scope Pekerjaan Lanjutan</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Tulis rincian perbaikan berdasarkan laporan teknisi sebelumnya...">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h5 class="card-title mt-4 mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                <span>Daftar Estimasi Jasa & Material Baru</span>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-item">
                    <i class="bi bi-plus"></i> Tambah Item
                </button>
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle" id="items-table">
                    <thead class="table-light text-center">
                        <tr>
                            <th style="width: 45%;">Deskripsi Jasa / Barang</th>
                            <th style="width: 12%;">Qty</th>
                            <th style="width: 15%;">Satuan</th>
                            <th style="width: 20%;">Harga Satuan</th>
                            <th style="width: 8%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="items-container">
                        @if(old('items'))
                            @foreach(old('items') as $index => $item)
                            <tr class="item-row">
                                <td>
                                    <input type="text" class="form-control form-control-sm @error("items.$index.description") is-invalid @enderror" name="items[{{ $index }}][description]" value="{{ $item['description'] }}" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-center quantity-field" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" min="1" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-center" name="items[{{ $index }}][unit]" value="{{ $item['unit'] }}" placeholder="unit, set, m">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-end price-field" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] }}" min="0" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            @endforeach
                        @else
                            <tr class="item-row">
                                <td>
                                    <input type="text" class="form-control form-control-sm" name="items[0][description]" placeholder="Jasa perbaikan / penggantian sparepart" required>
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-center quantity-field" name="items[0][quantity]" value="1" min="1" required>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-center" name="items[0][unit]" placeholder="unit, set, m">
                                </td>
                                <td>
                                    <input type="number" class="form-control form-control-sm text-end price-field" name="items[0][unit_price]" value="0" min="0" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" disabled><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success px-4" onclick="return confirm('Apakah Anda yakin ingin melanjutkan pekerjaan ini? Tindakan ini akan menggratiskan biaya pengecekan sebelumnya.')">Lanjutkan & Buat WO Baru</button>
                <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        let itemIndex = $('#items-container tr').length;

        $('#btn-add-item').on('click', function() {
            let newRow = `
            <tr class="item-row">
                <td>
                    <input type="text" class="form-control form-control-sm" name="items[${itemIndex}][description]" placeholder="Jasa perbaikan / penggantian sparepart" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-center quantity-field" name="items[${itemIndex}][quantity]" value="1" min="1" required>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm text-center" name="items[${itemIndex}][unit]" placeholder="unit, set, m">
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm text-end price-field" name="items[${itemIndex}][unit_price]" value="0" min="0" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button>
                </td>
            </tr>`;
            
            $('#items-container').append(newRow);
            itemIndex++;
            toggleRemoveButtons();
        });

        $('#items-container').on('click', '.btn-remove-item', function() {
            $(this).closest('tr').remove();
            toggleRemoveButtons();
        });

        function toggleRemoveButtons() {
            let rows = $('#items-container tr').length;
            if (rows <= 1) {
                $('#items-container .btn-remove-item').attr('disabled', true);
            } else {
                $('#items-container .btn-remove-item').removeAttr('disabled');
            }
        }
        
        toggleRemoveButtons();
    });
</script>
@endpush
