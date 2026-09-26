@extends('layouts.app')

@section('title', 'Invoice Gabungan')
@section('page-title', 'Invoice Gabungan')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Invoice Gabungan</h1>
            <p class="text-muted mb-0">Gabungkan beberapa Work Order menjadi satu invoice (fleksibel).</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="card-title">Filter Work Order</h5>
            <form action="{{ route('admin.combined-invoices.create') }}" method="GET" class="row">
                <div class="col-md-4 mb-3">
                    <label for="customer_id" class="form-label">Customer</label>
                    <select class="form-select" id="customer_id" name="customer_id">
                        <option value="">Semua Customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>
                                {{ $customer->display_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="from" class="form-label">Dari Tanggal</label>
                    <input type="text" class="form-control datepicker" id="from" name="from" value="{{ request('from') }}">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="to" class="form-label">Sampai Tanggal</label>
                    <input type="text" class="form-control datepicker" id="to" name="to" value="{{ request('to') }}">
                </div>
                <div class="col-md-2 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary w-100"><i class="bi bi-filter"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.combined-invoices.download') }}" method="POST">
                @csrf
                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-striped" id="wo-table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">
                                    <input type="checkbox" id="check-all" class="form-check-input">
                                </th>
                                <th>WO Number</th>
                                <th>Tanggal</th>
                                <th>Customer</th>
                                <th>Judul</th>
                                <th>Status</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($workOrders as $workOrder)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="work_order_ids[]" value="{{ $workOrder->id }}" class="form-check-input wo-check">
                                    </td>
                                    <td>{{ $workOrder->wo_number }}</td>
                                    <td>{{ $workOrder->scheduled_date?->format('d/m/Y') ?: '-' }}</td>
                                    <td>{{ $workOrder->customer->display_name }}</td>
                                    <td>{{ $workOrder->title }}</td>
                                    <td><x-status-badge :status="$workOrder->status" /></td>
                                    <td>Rp {{ number_format($workOrder->invoice ? $workOrder->invoice->total : $workOrder->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data Work Order yang ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        {{ $workOrders->links() }}
                    </div>
                    <button type="submit" class="btn btn-primary" id="btn-download" disabled>
                        <i class="bi bi-file-earmark-pdf me-1"></i> Download Invoice Gabungan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Handle select all
        $('#check-all').change(function() {
            $('.wo-check').prop('checked', $(this).prop('checked'));
            toggleDownloadBtn();
        });

        // Handle individual check
        $('.wo-check').change(function() {
            if ($('.wo-check:checked').length === $('.wo-check').length) {
                $('#check-all').prop('checked', true);
            } else {
                $('#check-all').prop('checked', false);
            }
            toggleDownloadBtn();
        });

        function toggleDownloadBtn() {
            $('#btn-download').prop('disabled', $('.wo-check:checked').length === 0);
        }
    });
</script>
@endpush
