<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice & Laporan Pekerjaan - {{ $workOrder->wo_number }}</title>
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
            font-size: 18px;
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
            margin-bottom: 20px;
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
        .notes-section {
            width: 55%;
            float: left;
            margin-top: 5px;
            font-size: 11px;
        }
        .clearfix {
            clear: both;
        }
        .page-break {
            page-break-before: always;
        }
        .report-section {
            margin-bottom: 20px;
            border: 1px solid #E2E8F0;
            border-radius: 4px;
            padding: 15px;
        }
        .report-meta {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
        }
        .report-label {
            font-weight: bold;
            color: #475569;
            margin-top: 10px;
            font-size: 11px;
            text-transform: uppercase;
        }
        .report-value {
            margin-bottom: 10px;
            white-space: pre-line;
        }
        .photos-container {
            margin-top: 15px;
        }
        .photo-wrapper {
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
            text-align: center;
            border: 1px solid #E2E8F0;
            padding: 5px;
            border-radius: 4px;
        }
        .photo-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 2px;
        }
        .photo-caption {
            font-size: 9px;
            color: #666;
            margin-top: 3px;
            max-width: 140px;
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
    </style>
</head>
<body>

    <!-- ==================== PAGE 1: INVOICE ==================== -->
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
                    @if($workOrder->invoice)
                        <strong>No:</strong> {{ $workOrder->invoice->invoice_number }}<br>
                        <strong>Tanggal:</strong> {{ $workOrder->invoice->created_at->format('d/m/Y') }}<br>
                        @if($workOrder->invoice->due_date)
                            <strong>Jatuh Tempo:</strong> {{ $workOrder->invoice->due_date->format('d/m/Y') }}<br>
                        @endif
                        <strong>Status:</strong> {{ $workOrder->invoice->payment_status->label() }}
                    @else
                        <strong>No. WO:</strong> {{ $workOrder->wo_number }}<br>
                        <strong>Status WO:</strong> {{ $workOrder->status->label() }}
                    @endif
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
                    <strong>{{ $workOrder->customer->name }}</strong><br>
                    @if($workOrder->customer->company_name)
                        {{ $workOrder->customer->company_name }}<br>
                    @endif
                    @if($workOrder->customer->address)
                        {{ $workOrder->customer->address }}<br>
                    @endif
                    Telp: {{ $workOrder->customer->phone }}
                </div>
            </td>
            <td>
                <div class="section-title">Referensi Pekerjaan</div>
                <div class="info-block">
                    <strong>No. WO:</strong> {{ $workOrder->wo_number }}<br>
                    <strong>Pekerjaan:</strong> {{ $workOrder->title }}<br>
                    <strong>Tipe:</strong> {{ $workOrder->type->label() }}<br>
                    <strong>Teknisi:</strong> 
                    @foreach($workOrder->assignments as $assignment)
                        {{ $assignment->technician->name }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    @if($workOrder->invoice)
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
            @foreach($workOrder->invoice->items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">{{ $item->unit ?: '-' }}</td>
                <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-end">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary / Footer Notes -->
    <div>
        <div class="notes-section">
            @if($workOrder->invoice->notes)
                <div style="font-weight: bold; margin-bottom: 5px;">Catatan:</div>
                <div style="color: #666; white-space: pre-line;">{{ $workOrder->invoice->notes }}</div>
            @endif
        </div>

        <table class="summary-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">Rp {{ number_format($workOrder->invoice->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($workOrder->invoice->discount > 0)
            <tr>
                <td class="label">Diskon</td>
                <td class="value">-Rp {{ number_format($workOrder->invoice->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($workOrder->invoice->tax_amount > 0)
            <tr>
                <td class="label">Pajak ({{ intval($workOrder->invoice->tax_percentage) }}%)</td>
                <td class="value">Rp {{ number_format($workOrder->invoice->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="label" style="color: #0369A1; font-weight: bold;">Total Tagihan</td>
                <td class="value">Rp {{ number_format($workOrder->invoice->total, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="clearfix"></div>
    </div>
    @else
    <div class="report-section text-center py-4" style="text-align: center; color: #999;">
        Invoice belum digenerate untuk Work Order ini.
    </div>
    @endif

    <!-- ==================== PAGE 2: REPORT ==================== -->
    <div class="page-break"></div>

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
                <div class="doc-title">Laporan Kerja</div>
                <div class="doc-details">
                    <strong>No. WO:</strong> {{ $workOrder->wo_number }}<br>
                    <strong>Tanggal:</strong> {{ $workOrder->scheduled_date ? $workOrder->scheduled_date->format('d/m/Y') : '-' }}<br>
                    <strong>Tipe:</strong> {{ $workOrder->type->label() }}
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Laporan Hasil Pekerjaan</div>

    @forelse($workOrder->reports as $report)
    <div class="report-section">
        <div class="report-meta">
            Dilaporkan oleh: <strong>{{ $report->technician->name }}</strong> pada {{ $report->submitted_at->format('d/m/Y H:i') }}
        </div>
        
        <div class="report-label">Temuan Lapangan (Findings)</div>
        <div class="report-value">{{ $report->findings }}</div>

        <div class="report-label">Pekerjaan Yang Dilakukan (Work Done)</div>
        <div class="report-value">{{ $report->work_done }}</div>

        @if($report->recommendations)
        <div class="report-label">Rekomendasi / Catatan Tambahan</div>
        <div class="report-value">{{ $report->recommendations }}</div>
        @endif

        @if($report->photos->count())
        <div class="report-label">Dokumentasi Foto</div>
        <div class="photos-container">
            @foreach($report->photos as $photo)
            <div class="photo-wrapper">
                <img class="photo-img" src="{{ public_path($photo->photo_path) }}" alt="Foto">
                <div class="photo-caption">
                    [{{ strtoupper($photo->photo_type->value) }}]
                    @if($photo->caption)
                        <br>{{ $photo->caption }}
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="report-section text-center py-4" style="text-align: center; color: #999;">
        Belum ada laporan dari teknisi.
    </div>
    @endforelse

    <!-- Page Footer -->
    <div class="footer">
        {{ $settings['invoice_footer'] }}
    </div>

</body>
</html>