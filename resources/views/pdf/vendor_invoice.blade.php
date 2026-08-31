<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Invoice Vendor - {{ $vendor->name }}</title>
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

        .photo {
            width: 82px;
            height: 82px;
            object-fit: cover;
            margin: 4px;
            border: 1px solid #dfe4ec;
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
                <h1>INVOICE</h1><strong>Tagihan Vendor Gabungan</strong><br><span class="muted">Periode:
                    {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</span>
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td><strong>Ditagihkan
                    kepada:</strong><br>{{ $vendor->name }}<br>{{ $vendor->address ?: '-' }}<br>{{ $vendor->phone ?: '-' }}
            </td>
            <td class="right"><strong>Tanggal terbit:</strong><br>{{ now()->format('d/m/Y') }}<br><strong>Jumlah
                    WO:</strong> {{ $workOrders->count() }}</td>
        </tr>
    </table>
    <table class="summary">
        <tr>
            <td>Total Tagihan</td>
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
                <th class="right">Total Vendor</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($workOrders as $workOrder)
                <tr>
                    <td>{{ $workOrder->wo_number }}<br>{{ $workOrder->scheduled_date?->format('d/m/Y') ?: '-' }}</td>
                    <td>{{ $workOrder->customer->display_name }}<br>{{ $workOrder->title }}</td>
                    <td>{{ $workOrder->assignments->pluck('technician.name')->implode(', ') ?: '-' }}</td>
                    <td class="right">Rp {{ number_format($workOrder->vendor_total, 0, ',', '.') }}</td>
                </tr>
                @foreach ($workOrder->items as $item)
                    <tr>
                        <td></td>
                        <td>{{ $item->description }} ({{ $item->quantity }} {{ $item->unit ?: 'unit' }})</td>
                        <td></td>
                        <td class="right">Rp
                            {{ number_format(($item->vendor_unit_price ?? 0) * $item->quantity, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    <div class="footer">{{ $settings['invoice_footer'] }}</div>

    <div class="report">
        <h2>Laporan Pekerjaan Vendor</h2>
        <p class="muted">{{ $vendor->name }} | {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</p>
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
                        <div>
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
