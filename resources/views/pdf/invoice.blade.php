<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
            line-height: 1.4;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td {
            vertical-align: top;
        }
        .brand {
            font-size: 24px;
            font-weight: bold;
            color: #0F172A;
            margin: 0;
        }
        .company-details {
            font-size: 11px;
            color: #666;
            margin-top: 5px;
        }
        .doc-title {
            font-size: 20px;
            font-weight: bold;
            color: #0369A1;
            text-align: right;
            margin: 0;
            text-transform: uppercase;
        }
        .doc-details {
            text-align: right;
            font-size: 11px;
            margin-top: 5px;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .details-table td {
            width: 50%;
            vertical-align: top;
        }
        .section-title {
            font-weight: bold;
            color: #0F172A;
            border-bottom: 2px solid #E2E8F0;
            padding-bottom: 5px;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
        }
        .info-block {
            line-height: 1.5;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .items-table th {
            background-color: #F1F5F9;
            color: #475569;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            border-bottom: 2px solid #E2E8F0;
            font-size: 11px;
            text-transform: uppercase;
        }
        .items-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #E2E8F0;
        }
        .items-table .text-center {
            text-align: center;
        }
        .items-table .text-end {
            text-align: right;
        }
        .summary-table {
            width: 40%;
            float: right;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .summary-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #E2E8F0;
        }
        .summary-table .label {
            color: #64748B;
        }
        .summary-table .value {
            text-align: right;
            font-weight: bold;
        }
        .summary-table .grand-total {
            background-color: #F8FAFC;
            font-size: 14px;
            color: #0369A1;
            border-top: 2px solid #0369A1;
            border-bottom: 2px solid #0369A1;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #94A3B8;
            border-top: 1px solid #E2E8F0;
            padding-top: 10px;
        }
        .notes-section {
            width: 55%;
            float: left;
            margin-top: 5px;
            font-size: 11px;
        }
        .clearfix {
            clear: both;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                <div class="brand">{{ $settings['company_name'] }}</div>
                <div class="company-details">
                    {{ $settings['company_address'] }}<br>
                    Telp: {{ $settings['company_phone'] }} | WA: {{ $settings['company_wa'] }}<br>
                    Email: {{ $settings['company_email'] }}
                </div>
            </td>
            <td>
                <div class="doc-title">Invoice</div>
                <div class="doc-details">
                    <strong>No:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Tanggal:</strong> {{ $invoice->created_at->format('d/m/Y') }}<br>
                    @if($invoice->due_date)
                        <strong>Jatuh Tempo:</strong> {{ $invoice->due_date->format('d/m/Y') }}<br>
                    @endif
                    <strong>Status:</strong> {{ $invoice->payment_status->label() }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Billing Info -->
    <table class="details-table">
        <tr>
            <td>
                <div class="section-title">Tagihan Kepada</div>
                <div class="info-block">
                    <strong>{{ $invoice->customer->name }}</strong><br>
                    @if($invoice->customer->company_name)
                        {{ $invoice->customer->company_name }}<br>
                    @endif
                    @if($invoice->customer->address)
                        {{ $invoice->customer->address }}<br>
                    @endif
                    Telp: {{ $invoice->customer->phone }}
                </div>
            </td>
            <td>
                <div class="section-title">Referensi Pekerjaan</div>
                <div class="info-block">
                    <strong>No. WO:</strong> {{ $invoice->workOrder->wo_number }}<br>
                    <strong>Pekerjaan:</strong> {{ $invoice->workOrder->title }}<br>
                    <strong>Tipe:</strong> {{ $invoice->workOrder->type->name }}<br>
                    <strong>Teknisi:</strong> 
                    @foreach($invoice->workOrder->assignments as $assignment)
                        {{ $assignment->technician->name }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    <!-- Items Table -->
    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 50%;">Deskripsi Jasa / Barang</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 15%;" class="text-center">Satuan</th>
                <th style="width: 25%;" class="text-end">Harga Satuan</th>
                <th style="width: 25%;" class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">{{ $item->unit ?: '-' }}</td>
                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">Tidak ada item invoice</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary / Footer Notes -->
    <div>
        <div class="notes-section">
            @if($invoice->notes)
                <div style="font-weight: bold; margin-bottom: 5px;">Catatan:</div>
                <div style="color: #666; white-space: pre-line;">{{ $invoice->notes }}</div>
            @endif
        </div>

        <table class="summary-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">Rp {{ number_format($invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($invoice->discount_value > 0)
            <tr>
                <td class="label">Diskon ({{ $invoice->discount_type === 'percent' ? intval($invoice->discount_value).'%' : '' }})</td>
                <td class="value">-Rp {{ number_format($invoice->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($invoice->tax_percentage > 0)
            <tr>
                <td class="label">PPN ({{ intval($invoice->tax_percentage) }}%)</td>
                <td class="value">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="label" style="color: #0369A1; font-weight: bold;">Total Tagihan</td>
                <td class="value">Rp {{ number_format($invoice->total, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="clearfix"></div>
    </div>

    <!-- Page Footer -->
    <div class="footer">
        {{ $settings['invoice_footer'] }}
    </div>

</body>
</html>
