<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Pekerjaan - {{ $workOrder->wo_number }}</title>
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
        .report-section {
            margin-bottom: 20px;
            border: 1px solid #E2E8F0;
            border-radius: 4px;
            padding: 15px;
        }
        .report-title {
            font-weight: bold;
            color: #0369A1;
            font-size: 13px;
            margin-bottom: 10px;
            border-bottom: 1px solid #E2E8F0;
            padding-bottom: 5px;
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
            margin-bottom: 5px;
            font-size: 11px;
            text-transform: uppercase;
            clear: both;
            display: block;
        }
        .report-value {
            margin-bottom: 10px;
            white-space: pre-line;
        }
        .photos-container {
            margin-top: 15px;
            clear: both;
            display: block;
        }
        .photo-wrapper {
            display: inline-block;
            vertical-align: top;
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
                    <strong>Tipe:</strong> {{ $workOrder->type->name }}
                </div>
            </td>
        </tr>
    </table>

    <!-- Billing Info -->
    <table class="details-table">
        <tr>
            <td>
                <div class="section-title">Pelanggan</div>
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
                <div class="section-title">Detail Pekerjaan</div>
                <div class="info-block">
                    <strong>Judul:</strong> {{ $workOrder->title }}<br>
                    <strong>Kategori:</strong> {{ $workOrder->serviceCategory->name }}<br>
                    <strong>Lokasi:</strong> {{ $workOrder->location }}<br>
                    <strong>Teknisi:</strong> 
                    @foreach($workOrder->assignments as $assignment)
                        {{ $assignment->technician->name }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Laporan Hasil Pekerjaan</div>

    @foreach($workOrder->reports as $report)
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
                {{-- In PDF generation we might need to convert public url or absolute path for dompdf --}}
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
    @endforeach

    <!-- Page Footer -->
    <div class="footer">
        {{ $settings['invoice_footer'] }}
    </div>

</body>
</html>