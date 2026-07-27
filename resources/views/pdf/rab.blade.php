<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>RAB - {{ $rab->rab_number }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; line-height: 1.4; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { vertical-align: top; }
        .brand { font-size: 24px; font-weight: bold; color: #0F172A; margin: 0; }
        .company-details { font-size: 11px; color: #666; margin-top: 5px; }
        .doc-title { font-size: 18px; font-weight: bold; color: #0369A1; text-align: right; margin: 0; text-transform: uppercase; }
        .doc-details { text-align: right; font-size: 11px; margin-top: 5px; }
        .details-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .details-table td { width: 50%; vertical-align: top; }
        .section-title { font-weight: bold; color: #0F172A; border-bottom: 2px solid #E2E8F0; padding-bottom: 5px; margin-bottom: 8px; text-transform: uppercase; font-size: 11px; letter-spacing: 0.05em; }
        .info-block { line-height: 1.5; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .items-table th { background-color: #F1F5F9; color: #475569; font-weight: bold; text-align: left; padding: 8px 10px; border-bottom: 2px solid #E2E8F0; font-size: 11px; text-transform: uppercase; }
        .items-table td { padding: 8px 10px; border-bottom: 1px solid #E2E8F0; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .category-row td { background: #F8FAFC; font-weight: bold; color: #0369A1; padding-top: 12px; border-bottom: 1px solid #CBD5E1; }
        .summary-table { width: 40%; float: right; border-collapse: collapse; margin-bottom: 20px; }
        .summary-table td { padding: 6px 10px; border-bottom: 1px solid #E2E8F0; }
        .summary-table .label { color: #64748B; }
        .summary-table .value { text-align: right; font-weight: bold; }
        .summary-table .grand-total { background-color: #F8FAFC; font-size: 14px; color: #0369A1; border-top: 2px solid #0369A1; border-bottom: 2px solid #0369A1; }
        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 10px; color: #94A3B8; border-top: 1px solid #E2E8F0; padding-top: 10px; }
        .notes-section { width: 55%; float: left; margin-top: 5px; font-size: 11px; }
        .clearfix { clear: both; }
    </style>
</head>
<body>

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
                <div class="doc-title">Rencana Anggaran Biaya</div>
                <div class="doc-details">
                    <strong>No:</strong> {{ $rab->rab_number }}<br>
                    <strong>Tanggal:</strong> {{ $rab->created_at->format('d/m/Y') }}<br>
                    @if($rab->valid_until)
                        <strong>Berlaku s/d:</strong> {{ $rab->valid_until->format('d/m/Y') }}<br>
                    @endif
                    <strong>Status:</strong> {{ $rab->status->label() }}
                </div>
            </td>
        </tr>
    </table>

    <table class="details-table">
        <tr>
            <td>
                <div class="section-title">Kepada</div>
                <div class="info-block">
                    <strong>{{ $rab->customer->name }}</strong><br>
                    @if($rab->customer->company_name)
                        {{ $rab->customer->company_name }}<br>
                    @endif
                    @if($rab->customer->address)
                        {{ $rab->customer->address }}<br>
                    @endif
                    Telp: {{ $rab->customer->phone }}
                </div>
            </td>
            <td>
                <div class="section-title">Detail Proyek</div>
                <div class="info-block">
                    <strong>Judul:</strong> {{ $rab->title }}<br>
                    <strong>No. WO:</strong> {{ $rab->workOrder->wo_number }}<br>
                    <strong>Lokasi:</strong> {{ $rab->workOrder->location }}<br>
                    @if($rab->description)
                        <strong>Scope:</strong> {{ $rab->description }}
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th style="width: 40%;">Deskripsi</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 12%;" class="text-center">Satuan</th>
                <th style="width: 19%;" class="text-end">Harga Satuan</th>
                <th style="width: 19%;" class="text-end">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grouped = $rab->items->groupBy(fn ($i) => $i->category ?: 'Lainnya');
            @endphp
            @forelse($grouped as $category => $items)
                <tr class="category-row">
                    <td colspan="5">{{ strtoupper($category) }}</td>
                </tr>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-center">{{ $item->unit ?: '-' }}</td>
                    <td class="text-end">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($item->total_price, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #999;">Tidak ada item RAB</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div>
        <div class="notes-section">
            @if($rab->notes)
                <div style="font-weight: bold; margin-bottom: 5px;">Catatan:</div>
                <div style="color: #666; white-space: pre-line;">{{ $rab->notes }}</div>
            @endif
            <div style="margin-top: 16px; color: #64748B; font-size: 10px;">
                RAB ini bersifat estimasi. Harga dapat berubah setelah survey lapangan.
            </div>
        </div>

        <table class="summary-table">
            <tr>
                <td class="label">Subtotal</td>
                <td class="value">Rp {{ number_format($rab->subtotal, 0, ',', '.') }}</td>
            </tr>
            @if($rab->discount > 0)
            <tr>
                <td class="label">Diskon</td>
                <td class="value">-Rp {{ number_format($rab->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            @if($rab->tax_amount > 0)
            <tr>
                <td class="label">Pajak ({{ intval($rab->tax_percentage) }}%)</td>
                <td class="value">Rp {{ number_format($rab->tax_amount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="grand-total">
                <td class="label" style="color: #0369A1; font-weight: bold;">Total Estimasi</td>
                <td class="value">Rp {{ number_format($rab->total, 0, ',', '.') }}</td>
            </tr>
        </table>
        <div class="clearfix"></div>
    </div>

    <div class="footer">
        {{ $settings['invoice_footer'] }}
    </div>

</body>
</html>
