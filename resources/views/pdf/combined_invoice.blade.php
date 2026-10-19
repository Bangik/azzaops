<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice Gabungan</title>
    <style>
        @page {
            margin: 28px 35px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #2b2b2b;
        }

        h1 {
            color: #414c6e;
            font-size: 25px;
            margin: 0;
        }

        h2 {
            color: #414c6e;
            font-size: 15px;
            margin: 0 0 8px;
        }

        h3 {
            color: #414c6e;
            font-size: 12px;
            margin: 18px 0 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .header td {
            vertical-align: top;
            padding-bottom: 14px;
        }

        .muted {
            color: #687386;
        }

        .right {
            text-align: right;
        }

        .summary {
            margin: 12px 0 18px;
        }

        .summary td {
            background: #414c6e;
            color: #fff;
            padding: 9px 12px;
        }

        .summary .total {
            background: #333c58;
            font-size: 13px;
            font-weight: bold;
        }

        .items th {
            background: #414c6e;
            color: #fff;
            padding: 8px;
            text-align: left;
        }

        .items td {
            border-bottom: 1px solid #e2e6ed;
            padding: 8px;
        }

        .items tr:nth-child(even) td {
            background: #f4f6f9;
        }

        .report {
            page-break-before: always;
        }

        .report-card {
            border: 1px solid #dfe4ec;
            padding: 12px;
            margin-bottom: 12px;
        }

        .report-card table td {
            vertical-align: top;
            padding: 2px 0;
        }

        .label {
            color: #687386;
            font-weight: bold;
            width: 23%;
        }

        .report-card p {
            margin: 4px 0 6px;
            line-height: 1.4;
            clear: both;
        }

        .photos-container {
            clear: both;
            display: block;
            margin-top: 10px;
            margin-bottom: 6px;
        }

        .photo {
            width: 82px;
            height: 82px;
            object-fit: cover;
            margin: 4px;
            border: 1px solid #dfe4ec;
            display: inline-block;
            vertical-align: top;
        }

        .footer {
            position: fixed;
            bottom: -12px;
            left: 0;
            right: 0;
            color: #8892a3;
            text-align: center;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <h2>{{ $settings['company_name'] }}</h2>
                <div class="muted">{{ $settings['company_address'] }}</div>
                <div class="muted">{{ $settings['company_phone'] }} | {{ $settings['company_email'] }}</div>
            </td>
            <td class="right">
                <h1>INVOICE</h1><strong>Tagihan Gabungan</strong>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td>
                @php
                    $uniqueCustomers = $workOrders->pluck('customer')->unique('id');
                @endphp
                <strong>Ditagihkan kepada:</strong><br>
                @foreach ($uniqueCustomers as $customer)
                    {{ $customer->display_name }} - {{ $customer->phone ?: '-' }}<br>
                @endforeach
            </td>
            <td class="right"><strong>Tanggal terbit:</strong><br>{{ now()->format('d/m/Y') }}<br><strong>Jumlah
                    WO:</strong> {{ $workOrders->count() }}</td>
        </tr>
    </table>
    <table class="summary">
        @if(isset($subtotal) && ($discount > 0 || $tax > 0))
            <tr>
                <td style="background: #f4f6f9; color: #414c6e; font-weight: bold; text-align: right; width: 75%;">Subtotal</td>
                <td class="right" style="background: #f4f6f9; color: #414c6e; font-weight: bold;">Rp {{ number_format($subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($discount > 0)
                <tr>
                    <td style="background: #f4f6f9; color: #414c6e; font-weight: bold; text-align: right; width: 75%;">Diskon</td>
                    <td class="right" style="background: #f4f6f9; color: #414c6e; font-weight: bold;">-Rp {{ number_format($discount, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($tax > 0)
                <tr>
                    <td style="background: #f4f6f9; color: #414c6e; font-weight: bold; text-align: right; width: 75%;">Pajak</td>
                    <td class="right" style="background: #f4f6f9; color: #414c6e; font-weight: bold;">Rp {{ number_format($tax, 0, ',', '.') }}</td>
                </tr>
            @endif
        @endif
        <tr>
            <td style="text-align: right; width: 75%;">Total Tagihan</td>
            <td class="right total">Rp {{ number_format($total, 0, ',', '.') }}</td>
        </tr>
    </table>
    <h3>Rincian Work Order</h3>
    <table class="items">
        <thead>
            <tr>
                <th>WO / Tanggal</th>
                <th>Customer / Pekerjaan</th>
                <th>Teknisi</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($workOrders as $workOrder)
                @php
                    $woTotal = $workOrder->invoice ? $workOrder->invoice->total : $workOrder->total;
                @endphp
                <tr>
                    <td>{{ $workOrder->wo_number }}<br>{{ $workOrder->scheduled_date?->format('d/m/Y') ?: '-' }}</td>
                    <td>{{ $workOrder->customer->display_name }}<br>{{ $workOrder->title }}</td>
                    <td>{{ $workOrder->assignments->pluck('technician.name')->implode(', ') ?: '-' }}</td>
                    <td class="right">Rp {{ number_format($woTotal, 0, ',', '.') }}</td>
                </tr>
                @if($workOrder->invoice)
                    @foreach ($workOrder->invoice->items as $item)
                        <tr>
                            <td></td>
                            <td>{{ $item->description }} ({{ $item->quantity }} {{ $item->unit ?: 'unit' }})</td>
                            <td></td>
                            <td class="right">Rp
                                {{ number_format(($item->unit_price ?? 0) * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                    @php
                        $woSubtotal = $workOrder->invoice->subtotal;
                        $woDiscount = $workOrder->invoice->discount;
                        $woTax = $workOrder->invoice->tax_amount;
                    @endphp
                    @if($woDiscount > 0 || $woTax > 0)
                        <tr>
                            <td></td>
                            <td style="text-align: right; font-style: italic; color: #687386;">Subtotal:</td>
                            <td></td>
                            <td class="right" style="font-style: italic; color: #687386;">Rp {{ number_format($woSubtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($woDiscount > 0)
                        <tr>
                            <td></td>
                            <td style="text-align: right; font-style: italic; color: #687386;">Diskon:</td>
                            <td></td>
                            <td class="right" style="font-style: italic; color: #687386;">-Rp {{ number_format($woDiscount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        @if($woTax > 0)
                        <tr>
                            <td></td>
                            <td style="text-align: right; font-style: italic; color: #687386;">Pajak:</td>
                            <td></td>
                            <td class="right" style="font-style: italic; color: #687386;">Rp {{ number_format($woTax, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                    @endif
                @else
                    @foreach ($workOrder->items as $item)
                        <tr>
                            <td></td>
                            <td>{{ $item->description }} ({{ $item->quantity }} {{ $item->unit ?: 'unit' }})</td>
                            <td></td>
                            <td class="right">Rp
                                {{ number_format(($item->unit_price ?? 0) * $item->quantity, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @endif
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ $settings['invoice_footer'] }}</div>

    <div class="report">
        <h2>Laporan Pekerjaan</h2>
        @foreach ($workOrders as $workOrder)
            <div class="report-card">
                <h3>{{ $workOrder->wo_number }} - {{ $workOrder->title }}</h3>
                <table>
                    <tr>
                        <td class="label">Customer</td>
                        <td>{{ $workOrder->customer->display_name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Lokasi</td>
                        <td>{{ $workOrder->location }}</td>
                    </tr>
                    <tr>
                        <td class="label">Status</td>
                        <td>{{ $workOrder->status->label() }}</td>
                    </tr>
                </table>
                @forelse($workOrder->reports as $report)
                    <p><strong>Teknisi:</strong> {{ $report->technician->name }} |
                        {{ $report->submitted_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Temuan:</strong> {{ $report->findings }}</p>
                    <p><strong>Pekerjaan:</strong> {{ $report->work_done }}</p>
                    @if ($report->recommendations)
                        <p><strong>Rekomendasi:</strong> {{ $report->recommendations }}</p>
                    @endif
                    @if ($report->photos->count())
                        <div class="photos-container">
                            @foreach ($report->photos as $photo)
                                <img class="photo" src="{{ public_path($photo->photo_path) }}">
                            @endforeach
                        </div>
                    @endif
                @empty
                    <p class="muted">Belum ada laporan teknisi.</p>
                @endforelse
            </div>
        @endforeach
    </div>
</body>

</html>
