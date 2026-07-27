@extends('layouts.app')

@section('title', 'Buat RAB')
@section('page-title', 'Buat RAB')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Buat RAB</h1>
        <p class="text-muted mb-0">Dari WO {{ $workOrder->wo_number }} — {{ $workOrder->customer->name }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.rab.store') }}" method="POST">
            @csrf
            <input type="hidden" name="work_order_id" value="{{ $workOrder->id }}">

            <div class="row mb-3">
                <div class="col-md-6">
                    <label class="form-label">Judul RAB</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', 'RAB ' . $workOrder->title) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Berlaku s/d</label>
                    <input type="text" name="valid_until" class="form-control datepicker" value="{{ old('valid_until') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Pajak (%)</label>
                    <input type="number" name="tax_percentage" class="form-control" value="{{ old('tax_percentage', 0) }}" min="0" max="100" step="0.01">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <label class="form-label">Deskripsi / Scope</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $workOrder->description) }}</textarea>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Diskon (Rp)</label>
                    <input type="number" name="discount" class="form-control" value="{{ old('discount', 0) }}" min="0">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Item RAB</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-item"><i class="bi bi-plus"></i> Tambah Item</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:15%">Kategori</th>
                            <th>Deskripsi</th>
                            <th style="width:10%">Qty</th>
                            <th style="width:12%">Satuan</th>
                            <th style="width:15%">Harga</th>
                            <th style="width:8%"></th>
                        </tr>
                    </thead>
                    <tbody id="items-container">
                        <tr>
                            <td>
                                <select name="items[0][category]" class="form-select form-select-sm" required>
                                    <option value="Material">Material</option>
                                    <option value="Jasa">Jasa</option>
                                    <option value="Transport">Transport</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </td>
                            <td><input type="text" name="items[0][description]" class="form-control form-control-sm" required></td>
                            <td><input type="number" name="items[0][quantity]" class="form-control form-control-sm" value="1" min="1" required></td>
                            <td><input type="text" name="items[0][unit]" class="form-control form-control-sm"></td>
                            <td><input type="number" name="items[0][unit_price]" class="form-control form-control-sm" value="0" min="0" required></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item" disabled><i class="bi bi-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary">Simpan RAB</button>
            <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    let itemIndex = 1;
    $('#btn-add-item').on('click', function () {
        $('#items-container').append(`
            <tr>
                <td>
                    <select name="items[${itemIndex}][category]" class="form-select form-select-sm" required>
                        <option value="Material">Material</option>
                        <option value="Jasa">Jasa</option>
                        <option value="Transport">Transport</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </td>
                <td><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" required></td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" value="1" min="1" required></td>
                <td><input type="text" name="items[${itemIndex}][unit]" class="form-control form-control-sm"></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm" value="0" min="0" required></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
            </tr>
        `);
        itemIndex++;
        $('#items-container .btn-remove-item').prop('disabled', false);
    });
    $('#items-container').on('click', '.btn-remove-item', function () {
        if ($('#items-container tr').length > 1) {
            $(this).closest('tr').remove();
            if ($('#items-container tr').length === 1) {
                $('#items-container .btn-remove-item').prop('disabled', true);
            }
        }
    });
});
</script>
@endpush
