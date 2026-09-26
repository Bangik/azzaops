<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $documentTitle ?? 'INVOICE & LAPORAN' }} @isset($invoiceNumber) {{ $invoiceNumber }} @endisset</title>
    <style>
        @page { margin: 0; size: A4; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #2b2b2b; }
        table { border-collapse: collapse; width: 100%; }
        .accent-bar { background-color: #a9cdd6; height: 14px; width: 100%; }
        
        /* HEADER */
        .header { background-color: #414c6e; color: #ffffff; padding: 28px 40px 22px 40px; }
        .logo-mark { width: 30px; height: 30px; background-color: #a9cdd6; }
        .company-name { font-size: 17px; font-weight: bold; letter-spacing: 0.5px; }
        .company-tagline { font-size: 9px; color: #cdd6e4; margin-top: 2px; }
        .invoice-title { font-size: 42px; font-weight: bold; text-align: right; letter-spacing: 3px; }
        .invoice-no { text-align: right; font-size: 11px; font-weight: bold; margin-top: 6px; }
        .header-divider { border-top: 1px solid rgba(255, 255, 255, 0.35); margin: 20px 0 16px 0; }
        .contact-heading { font-size: 12px; font-weight: bold; margin-bottom: 10px; }
        .contact-label { font-size: 8px; font-weight: bold; color: #cdd6e4; text-transform: uppercase; margin-bottom: 3px; }
        .contact-value { font-size: 10px; font-weight: bold; margin-bottom: 8px; }
        .date-label { font-size: 11px; font-weight: bold; text-align: right; }
        .date-value { font-size: 10px; text-align: right; margin-top: 2px; margin-bottom: 10px; }
        
        /* BILLING */
        .billing-section { padding: 26px 40px 22px 40px; }
        .kepada-label { font-size: 10px; font-weight: bold; letter-spacing: 0.5px; margin-bottom: 6px; }
        .client-name { font-size: 15px; font-weight: bold; margin-bottom: 10px; }
        .client-detail { font-size: 10px; margin-bottom: 4px; }
        .amount-label { font-size: 10px; font-weight: bold; letter-spacing: 0.5px; text-align: right; margin-bottom: 6px; }
        .amount-value { font-size: 26px; font-weight: bold; color: #414c6e; text-align: right; }
        
        /* ITEMS */
        .content-wrap { padding: 0 40px; }
        .items-table thead { display: table-header-group; }
        .items-table tbody { display: table-row-group; }
        .items-table thead th { background-color: #414c6e; color: #ffffff; font-size: 10px; font-weight: bold; padding: 12px 14px; text-align: left; }
        .items-table thead th.col-total { background-color: #a9cdd6; color: #414c6e; }
        .items-table thead th.center, .items-table tbody td.center { text-align: center; }
        .items-table tbody td { padding: 14px; font-size: 10px; border-bottom: 1px solid #e8ebf0; }
        .items-table tbody tr:nth-child(even) td { background-color: #f4f6f9; }
        .item-name { font-weight: bold; font-size: 11px; }
        .item-desc { font-size: 9px; color: #8a8a8a; margin-top: 2px; }
        
        /* FOOTER */
        .footer-section { padding: 24px 40px 16px 40px; }
        .payment-heading { font-size: 11px; font-weight: bold; margin-bottom: 10px; }
        .payment-label { font-size: 9px; font-weight: bold; }
        .payment-value { font-size: 9px; margin-bottom: 8px; }
        .signature-img { height: 45px; margin: 14px 0 4px 0; }
        .signature-line { border-top: 1px solid #333333; width: 190px; margin-top: 42px; }
        .signature-name { font-size: 11px; font-weight: bold; margin-top: 6px; }
        .signature-position { font-size: 9px; color: #666666; }
        
        .summary-box td { font-size: 10px; color: #ffffff; background-color: #414c6e; padding: 9px 16px; }
        .summary-box tr.total td { font-weight: bold; font-size: 12px; background-color: #333c58; border-top: 1px solid rgba(255, 255, 255, 0.35); }
        .summary-label { text-align: left; }
        .summary-value { text-align: right; font-weight: bold; }
        .thanks { font-size: 12px; font-weight: bold; text-align: right; margin-top: 22px; }
        .terms { font-size: 9px; font-style: italic; color: #555555; text-align: right; margin-top: 4px; }
        
        /* REPORT SECTION */
        .report-card { margin-bottom: 20px; border: 1px solid #414c6e; border-radius: 4px; overflow: hidden; page-break-inside: avoid; }
        .report-header { background-color: #414c6e; color: #ffffff; padding: 10px 15px; font-weight: bold; font-size: 11px; }
        .report-header-date { float: right; font-weight: normal; color: #cdd6e4; }
        .report-body { padding: 15px; }
        .report-section-title { font-weight: bold; font-size: 10px; color: #414c6e; margin-bottom: 5px; }
        .report-text { font-size: 10px; margin-bottom: 15px; line-height: 1.4; white-space: pre-line; }
        .photo-wrap { display: inline-block; vertical-align: top; margin-right: 10px; margin-bottom: 10px; text-align: center; border: 1px solid #e8ebf0; padding: 5px; background: #fff; }
        .photo-img { width: 140px; height: 140px; object-fit: cover; border-radius: 2px; }
        .photo-caption { font-size: 9px; color: #666; margin-top: 4px; max-width: 140px; }
    </style>
</head>
<body>
    @php
        $documentTitle = $documentTitle ?? 'INVOICE';
        $documentStatus = $documentStatus ?? null;
        $amountLabel = $amountLabel ?? 'JUMLAH YANG HARUS DIBAYAR';
        $dateLabel = $dateLabel ?? 'Tanggal';
        $subtotal = $subtotal ?? 0;
        $total = $total ?? 0;
        $discount = $discount ?? 0;
        $tax = $tax ?? 0;

        $rupiah = function ($number) {
            return 'Rp ' . number_format((float) $number, 0, ',', '.');
        };
    @endphp

    <!-- PAGE 1: INVOICE -->
    <div class="accent-bar"></div>
    <div class="header">
        <table>
            <tr>
                <td style="width:60%; vertical-align:middle;">
                    <table>
                        <tr>
                            <td style="width:34px; vertical-align:middle;">
                                @if (!empty($company['logo']))
                                    <img src="{{ $company['logo'] }}" style="width:30px; height:30px;">
                                @else
                                    <div class="logo-mark"></div>
                                @endif
                            </td>
                            <td style="vertical-align:middle; padding-left:10px;">
                                <div class="company-name">{{ strtoupper($company['name']) }}</div>
                                <div class="company-tagline">{{ $company['tagline'] }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <div class="invoice-title">{{ $documentTitle }}</div>
                    <div class="invoice-no">NO: {{ $invoiceNumber }}</div>
                    @if ($documentStatus)
                        <div class="invoice-no" style="color:#a9cdd6; margin-top:10px;">{{ $documentStatus }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <div class="header-divider"></div>

        <table>
            <tr>
                <td style="width:60%; vertical-align:top;">
                    <div class="contact-heading">Rincian Kontak</div>
                    <table>
                        <tr>
                            <td style="width:33%; vertical-align:top;">
                                <div class="contact-label">Phone</div>
                                <div class="contact-value">{{ $company['phone'] }}</div>
                            </td>
                            <td style="width:33%; vertical-align:top;">
                                <div class="contact-label">Email</div>
                                <div class="contact-value">{{ $company['email'] }}</div>
                            </td>
                            <td style="width:34%; vertical-align:top;">
                                <div class="contact-label">Address</div>
                                <div class="contact-value">{{ $company['address'] }}</div>
                            </td>
                        </tr>
                    </table>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <div class="date-label">{{ $dateLabel }}</div>
                    <div class="date-value">{{ $invoiceDate }}</div>
                    <div class="date-label">Tanggal Jatuh Tempo</div>
                    <div class="date-value" style="margin-bottom:0;">{{ $dueDate }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="accent-bar"></div>

    <div class="billing-section">
        <table>
            <tr>
                <td style="width:60%; vertical-align:top;">
                    <div class="kepada-label">KEPADA:</div>
                    <div class="client-name">{{ $client['name'] }}</div>
                    <div class="client-detail"><strong>A:</strong> {{ $client['address'] }}</div>
                    <div class="client-detail"><strong>P:</strong> {{ $client['phone'] }} &nbsp;&nbsp;&nbsp;
                        <strong>E:</strong> {{ $client['email'] }}
                    </div>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <div class="amount-label">{{ $amountLabel }}</div>
                    <div class="amount-value">{{ $rupiah($total) }}</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="content-wrap">
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:46%;">Keterangan</th>
                    <th class="center" style="width:18%;">Harga Unit</th>
                    <th class="center" style="width:16%;">Kuantitas</th>
                    <th class="center col-total" style="width:20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item['name'] }}</div>
                            @if (!empty($item['description']))
                                <div class="item-desc">{{ $item['description'] }}</div>
                            @endif
                        </td>
                        <td class="center">{{ $rupiah($item['price']) }}</td>
                        <td class="center">{{ $item['qty'] }}</td>
                        <td class="center">{{ $rupiah($item['price'] * $item['qty']) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="footer-section">
        <table>
            <tr>
                <td style="width:55%; vertical-align:top;">
                    <div class="payment-heading">Metode Pembayaran</div>
                    @if (!empty($payment['method']))
                        <div class="payment-label">METODE:</div>
                        <div class="payment-value">{{ strtoupper($payment['method']) }}</div>
                    @endif
                    <table>
                        <tr>
                            <td style="width:50%; vertical-align:top;">
                                <div class="payment-label">KODE BANK:</div>
                                <div class="payment-value">{{ $payment['bank_code'] }}</div>
                                <div class="payment-label">No. Rek:</div>
                                <div class="payment-value">{{ $payment['account_number'] }}</div>
                            </td>
                            <td style="width:50%; vertical-align:top;">
                                <div class="payment-label">BANK:</div>
                                <div class="payment-value">{{ $payment['bank_name'] }}</div>
                                <div class="payment-label">Email:</div>
                                <div class="payment-value">{{ $payment['email'] }}</div>
                            </td>
                        </tr>
                    </table>

                    @if (!empty($signature['image']))
                        <img src="{{ $signature['image'] }}" class="signature-img">
                    @else
                        <div class="signature-line"></div>
                    @endif
                    <div class="signature-name">{{ $signature['name'] }}</div>
                    <div class="signature-position">{{ $signature['position'] }}</div>
                </td>
                <td style="width:45%; vertical-align:top;">
                    <table class="summary-box">
                        <tr>
                            <td class="summary-label">Sub Total :</td>
                            <td class="summary-value">{{ $rupiah($subtotal) }}</td>
                        </tr>
                        @if ($discount > 0)
                            <tr>
                                <td class="summary-label">Diskon{{ $discountLabel ? " ({$discountLabel})" : '' }} :</td>
                                <td class="summary-value">-{{ $rupiah($discount) }}</td>
                            </tr>
                        @endif
                        @if ($tax > 0)
                            <tr>
                                <td class="summary-label">Pajak :</td>
                                <td class="summary-value">{{ $rupiah($tax) }}</td>
                            </tr>
                        @endif
                        <tr class="total">
                            <td class="summary-label">Total :</td>
                            <td class="summary-value">{{ $rupiah($total) }}</td>
                        </tr>
                    </table>

                    <div class="thanks">TERIMA KASIH</div>
                    <div class="terms">Term &amp; Condition</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="accent-bar"></div>

    <!-- PAGE 2: REPORT -->
    <div style="page-break-before: always;"></div>

    <div class="accent-bar"></div>
    <div class="header">
        <table>
            <tr>
                <td style="width:60%; vertical-align:middle;">
                    <table>
                        <tr>
                            <td style="width:34px; vertical-align:middle;">
                                @if (!empty($company['logo']))
                                    <img src="{{ $company['logo'] }}" style="width:30px; height:30px;">
                                @else
                                    <div class="logo-mark"></div>
                                @endif
                            </td>
                            <td style="vertical-align:middle; padding-left:10px;">
                                <div class="company-name">{{ strtoupper($company['name']) }}</div>
                                <div class="company-tagline">{{ $company['tagline'] }}</div>
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
                    <div class="contact-label">Tipe:</div>
                    <div class="contact-value" style="margin-bottom:0;">{{ $workOrder->type->name ?? '-' }}</div>
                </td>
                <td style="width:40%; vertical-align:top;">
                    <div class="date-label">Tanggal Pelaksanaan</div>
                    <div class="date-value">{{ $workOrder->scheduled_date ? $workOrder->scheduled_date->format('d/m/Y') : '-' }}</div>
                    <div class="date-label">Teknisi</div>
                    <div class="date-value" style="margin-bottom:0;">
                        @foreach($workOrder->assignments as $assignment)
                            {{ $assignment->technician->name }}{{ !$loop->last ? ', ' : '' }}
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>
    </div>
    
    <div class="accent-bar"></div>

    <div class="content-wrap" style="padding-top: 25px;">
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

                    @if($report->recommendations)
                        <div class="report-section-title">Rekomendasi / Catatan Tambahan</div>
                        <div class="report-text">{{ $report->recommendations }}</div>
                    @endif

                    @if($report->photos->count())
                        <div class="report-section-title">Dokumentasi Foto</div>
                        <div style="margin-top: 5px; clear: both; display: block;">
                            @foreach($report->photos as $photo)
                                <div class="photo-wrap">
                                    <img src="{{ public_path($photo->photo_path) }}" class="photo-img">
                                    <div class="photo-caption">
                                        [{{ strtoupper($photo->photo_type->value) }}]
                                        @if($photo->caption)<br>{{ $photo->caption }}@endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 40px; color: #8a8a8a; border: 1px dashed #cdd6e4;">
                Belum ada laporan dari teknisi.
            </div>
        @endforelse
    </div>

</body>
</html>