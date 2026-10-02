<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Laporan Pekerjaan - {{ $workOrder->wo_number }}</title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #2b2b2b;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .accent-bar {
            background-color: #a9cdd6;
            height: 14px;
            width: 100%;
        }

        .header {
            background-color: #414c6e;
            color: #ffffff;
            padding: 28px 40px 22px 40px;
        }

        .logo-mark {
            width: 30px;
            height: 30px;
            background-color: #a9cdd6;
        }

        .company-name {
            font-size: 17px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .company-tagline {
            font-size: 9px;
            color: #cdd6e4;
            margin-top: 2px;
        }

        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            text-align: right;
            letter-spacing: 3px;
            text-transform: uppercase;
        }

        .invoice-no {
            text-align: right;
            font-size: 11px;
            font-weight: bold;
            margin-top: 6px;
        }

        .header-divider {
            border-top: 1px solid rgba(255, 255, 255, 0.35);
            margin: 20px 0 16px 0;
        }

        .contact-heading {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .contact-label {
            font-size: 8px;
            font-weight: bold;
            color: #cdd6e4;
            text-transform: uppercase;
            margin-bottom: 3px;
        }

        .contact-value {
            font-size: 10px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .date-label {
            font-size: 11px;
            font-weight: bold;
            text-align: right;
        }

        .date-value {
            font-size: 10px;
            text-align: right;
            margin-top: 2px;
            margin-bottom: 10px;
        }

        .content-wrap {
            padding: 0 40px;
        }

        .report-card {
            margin-bottom: 20px;
            border: 1px solid #414c6e;
            border-radius: 4px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .report-header {
            background-color: #414c6e;
            color: #ffffff;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 11px;
        }

        .report-header-date {
            float: right;
            font-weight: normal;
            color: #cdd6e4;
        }

        .report-body {
            padding: 15px;
        }

        .report-section-title {
            font-weight: bold;
            font-size: 10px;
            color: #414c6e;
            margin-bottom: 5px;
        }

        .report-text {
            font-size: 10px;
            margin-bottom: 15px;
            line-height: 1.4;
            white-space: pre-line;
        }

        .photo-wrap {
            display: inline-block;
            vertical-align: top;
            margin-right: 10px;
            margin-bottom: 10px;
            text-align: center;
            border: 1px solid #e8ebf0;
            padding: 5px;
            background: #fff;
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
            margin-top: 4px;
            max-width: 140px;
        }

        .footer-section {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 9px;
            color: #8a8a8a;
            padding: 10px;
            background-color: #f4f6f9;
            border-top: 1px solid #e8ebf0;
        }
    </style>
</head>

<body>
    @php
        $logo = $settings['company_logo'] ?? null;
        if ($logo && !str_starts_with($logo, 'data:') && !filter_var($logo, FILTER_VALIDATE_URL)) {
            $logo = public_path(ltrim($logo, '/'));
        }
    @endphp

    <div class="accent-bar"></div>
    <div class="header">
        <table>
            <tr>
                <td style="width:60%; vertical-align:middle;">
                    <table>
                        <tr>
                            <td style="width:34px; vertical-align:middle;">
                                @if ($logo)
                                    <img src="{{ $logo }}" style="width:30px; height:30px;">
                                @else
                                    <div class="logo-mark"></div>
                                @endif
                            </td>
                            <td style="vertical-align:middle; padding-left:10px;">
                                <div class="company-name">{{ strtoupper($settings['company_name'] ?? 'PERUSAHAAN') }}
                                </div>
                                <div class="company-tagline">{{ $settings['company_tagline'] ?? '' }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <div class="invoice-title">LAPORAN WO</div>
                    <div class="invoice-no">NO: {{ $workOrder->wo_number }}</div>
                </td>
            </tr>
        </table>

        <div class="header-divider"></div>

        <table>
            <tr>
                <td style="width:60%; vertical-align:top;">
                    <div class="contact-heading">Rincian Pekerjaan</div>
                    <div class="contact-label">Pekerjaan:</div>
                    <div class="contact-value">{{ $workOrder->title }}</div>
                    <div class="contact-label">Klien:</div>
                    <div class="contact-value">{{ $workOrder->customer->display_name ?? $workOrder->customer->name }}
                    </div>
                    <div class="contact-label">Tipe:</div>
                    <div class="contact-value" style="margin-bottom:0;">{{ $workOrder->type->name ?? '-' }}</div>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <div class="date-label">Tanggal Pelaksanaan</div>
                    <div class="date-value">
                        {{ $workOrder->scheduled_date ? $workOrder->scheduled_date->format('d/m/Y') : '-' }}</div>
                    <div class="date-label">Teknisi</div>
                    <div class="date-value" style="margin-bottom:0;">
                        @forelse($workOrder->assignments as $assignment)
                            {{ $assignment->technician->name }}{{ !$loop->last ? ', ' : '' }}
                        @empty
                            -
                        @endforelse
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="accent-bar"></div>

    <div class="content-wrap" style="padding-top: 25px; padding-bottom: 40px;">
        @forelse($workOrder->reports as $report)
            <div class="report-card">
                <div class="report-header">
                    Laporan oleh {{ $report->technician->name }}
                    <span class="report-header-date">{{ $report->submitted_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="report-body">
                    <div class="report-section-title">Temuan Lapangan (Findings)</div>
                    <div class="report-text">{{ $report->findings }}</div>

                    <div class="report-section-title">Pekerjaan Yang Dilakukan (Work Done)</div>
                    <div class="report-text">{{ $report->work_done }}</div>

                    @if ($report->recommendations)
                        <div class="report-section-title">Rekomendasi / Catatan Tambahan</div>
                        <div class="report-text">{{ $report->recommendations }}</div>
                    @endif

                    @if ($report->materials_used)
                        <div class="report-section-title">Material / Sparepart Yang Digunakan</div>
                        <div class="report-text">{{ $report->materials_used }}</div>
                    @endif

                    @if ($report->photos->count())
                        <div class="report-section-title">Dokumentasi Foto</div>
                        <div style="margin-top: 5px; clear: both; display: block;">
                            @foreach ($report->photos as $photo)
                                <div class="photo-wrap">
                                    <img src="{{ public_path($photo->photo_path) }}" class="photo-img">
                                    <div class="photo-caption">
                                        [{{ strtoupper($photo->photo_type->value) }}]
                                        @if ($photo->caption)
                                            <br>{{ $photo->caption }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #8a8a8a; border: 1px dashed #cdd6e4;">
                Belum ada laporan dari teknisi untuk pekerjaan ini.
            </div>
        @endforelse
    </div>

    <div class="footer-section">
        {{ $settings['invoice_footer'] ?? 'Terima kasih atas kepercayaan Anda.' }}
    </div>

</body>

</html>
