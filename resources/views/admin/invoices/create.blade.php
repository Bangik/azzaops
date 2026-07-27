@extends('layouts.app')

@section('title', 'Generate Invoice')
@section('page-title', 'Generate Invoice')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Generate Invoice</h1>
        <p class="text-muted mb-0">Dari WO {{ $workOrder->wo_number }} — {{ $workOrder->customer->name }}</p>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.invoices.store') }}" method="POST">
            @csrf
            <input type="hidden" name="work_order_id" value="{{ $workOrder->id }}">

            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">Jatuh Tempo</label>
                    <input type="text" name="due_date" class="form-control datepicker" value="{{ old('due_date') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Diskon (Rp)</label>
                    <input type="number" name="discount" class="form-control" value="{{ old('discount', 0) }}" min="0">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Pajak (%)</label>
                    <input type="number" name="tax_percentage" class="form-control" value="{{ old('tax_percentage', 0) }}" min="0" max="100" step="0.01">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Catatan Invoice</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Item Invoice</h5>
                <button type="button" class="btn btn-outline-primary btn-sm" id="btn-add-item"><i class="bi bi-plus"></i> Tambah Item</button>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Deskripsi</th>
                            <th style="width:12%">Qty</th>
                            <th style="width:15%">Satuan</th>
                            <th style="width:18%">Harga</th>
                            <th style="width:8%"></th>
                        </tr>
                    </thead>
                    <tbody id="items-container">
                        @foreach($workOrder->items as $i => $item)
                        <tr>
                            <td><input type="text" name="items[{{ $i }}][description]" class="form-control form-control-sm" value="{{ old("items.$i.description", $item->description) }}" required></td>
                            <td><input type="number" name="items[{{ $i }}][quantity]" class="form-control form-control-sm" value="{{ old("items.$i.quantity", $item->quantity) }}" min="1" required></td>
                            <td><input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm" value="{{ old("items.$i.unit", $item->unit) }}"></td>
                            <td><input type="number" name="items[{{ $i }}][unit_price]" class="form-control form-control-sm" value="{{ old("items.$i.unit_price", intval($item->unit_price)) }}" min="0" required></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                <button type="submit" class="btn btn-primary">Generate Invoice</button>
                <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    let itemIndex = $('#items-container tr').length;
    $('#btn-add-item').on('click', function () {
        $('#items-container').append(`
            <tr>
                <td><input type="text" name="items[${itemIndex}][description]" class="form-control form-control-sm" required></td>
                <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control form-control-sm" value="1" min="1" required></td>
                <td><input type="text" name="items[${itemIndex}][unit]" class="form-control form-control-sm"></td>
                <td><input type="number" name="items[${itemIndex}][unit_price]" class="form-control form-control-sm" value="0" min="0" required></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger btn-remove-item"><i class="bi bi-trash"></i></button></td>
            </tr>
        `);
        itemIndex++;
    });
    $('#items-container').on('click', '.btn-remove-item', function () {
        if ($('#items-container tr').length > 1) $(this).closest('tr').remove();
    });
});
</script>
@endpush
