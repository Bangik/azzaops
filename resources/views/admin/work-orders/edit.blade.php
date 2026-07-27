@extends('layouts.app')

@section('title', 'Edit Work Order')
@section('page-title', 'Edit Work Order')

@section('content')
<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.work-orders.update', $workOrder) }}" method="POST">
            @csrf
            @method('PUT')
            
            <h5 class="card-title mb-3 border-bottom pb-2">Informasi Pekerjaan ({{ $workOrder->wo_number }})</h5>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                        <option value="">Pilih Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $workOrder->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->display_name }} ({{ $customer->phone }})
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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
                    <label for="type" class="form-label">Tipe Pekerjaan</label>
                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type" required>
                        @foreach(App\Enums\WorkOrderType::cases() as $type)
                            <option value="{{ $type->value }}" {{ old('type', $workOrder->type->value) == $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="scheduled_date" class="form-label">Tanggal Rencana</label>
                    <input type="text" class="form-control datepicker @error('scheduled_date') is-invalid @enderror" id="scheduled_date" name="scheduled_date" value="{{ old('scheduled_date', $workOrder->scheduled_date ? $workOrder->scheduled_date->format('Y-m-d') : '') }}">
                    @error('scheduled_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-4 mb-3">
                    <label for="priority" class="form-label">Prioritas</label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                        @foreach(App\Enums\WorkOrderPriority::cases() as $priority)
                            <option value="{{ $priority->value }}" {{ old('priority', $workOrder->priority->value) == $priority->value ? 'selected' : '' }}>
                                {{ $priority->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label for="title" class="form-label">Judul Singkat Pekerjaan</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title', $workOrder->title) }}" placeholder="Contoh: Perbaikan AC Bocor, Cuci AC 2 Unit" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label for="location" class="form-label">Alamat Lengkap Lokasi</label>
                    <textarea class="form-control @error('location') is-invalid @enderror" id="location" name="location" rows="2" required>{{ old('location', $workOrder->location) }}</textarea>
                    @error('location')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-12 mb-3">
                    <label for="description" class="form-label">Deskripsi & Keluhan Detail</label>
                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $workOrder->description) }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <h5 class="card-title mt-4 mb-3 border-bottom pb-2 d-flex justify-content-between align-items-center">
                <span>Daftar Pekerjaan / Jasa & Material</span>
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
                        @php
                            $items = old('items', $workOrder->items->toArray());
                        @endphp
                        @foreach($items as $index => $item)
                        <tr class="item-row">
                            <td>
                                <input type="text" class="form-control form-control-sm" name="items[{{ $index }}][description]" value="{{ $item['description'] }}" required>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-center quantity-field" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] }}" min="1" required>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm text-center" name="items[{{ $index }}][unit]" value="{{ $item['unit'] }}" placeholder="unit, set, m">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm text-end price-field" name="items[{{ $index }}][unit_price]" value="{{ intval($item['unit_price']) }}" min="0" required>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
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
                    <input type="text" class="form-control form-control-sm" name="items[${itemIndex}][description]" placeholder="Jasa pengecekan / cuci AC / sparepart" required>
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
