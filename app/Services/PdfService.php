<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Rab;
use App\Models\Setting;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfService
{
    public function generateInvoicePdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'workOrder.assignments.technician', 'items', 'issuer', 'financialAccount']);
        $settings = $this->getCompanySettings();
        $data = $this->getInvoiceV2Data($invoice, $settings);

        $pdf = Pdf::loadView('pdf.invoicev2', $data)
            ->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function generateReceiptPdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'workOrder.assignments.technician', 'items', 'issuer', 'financialAccount']);
        $settings = $this->getCompanySettings();
        $data = $this->getInvoiceV2Data($invoice, $settings, true);

        return Pdf::loadView('pdf.invoicev2', $data)
            ->setPaper('a4', 'portrait');
    }

    public function generateRabPdf(Rab $rab)
    {
        $rab->load(['customer', 'workOrder', 'items', 'creator']);
        $settings = $this->getCompanySettings();

        $pdf = Pdf::loadView('pdf.rab', compact('rab', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function generateReportPdf(WorkOrder $workOrder)
    {
        $workOrder->load(['customer', 'serviceCategory', 'creator', 'assignments.technician', 'reports.technician', 'reports.photos']);
        $settings = $this->getCompanySettings();

        $pdf = Pdf::loadView('pdf.report_v2', compact('workOrder', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function generateInvoiceReportPdf(WorkOrder $workOrder)
    {
        $workOrder->load(['customer', 'serviceCategory', 'creator', 'assignments.technician', 'reports.technician', 'reports.photos', 'invoice.items', 'invoice.issuer', 'invoice.financialAccount']);
        $settings = $this->getCompanySettings();
        
        $data = $this->getInvoiceV2Data($workOrder->invoice, $settings);
        $data['workOrder'] = $workOrder;
        $data['settings'] = $settings;

        $pdf = Pdf::loadView('pdf.invoice_report_v2', $data)
            ->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function generateVendorInvoicePdf($vendor, $workOrders, string $from, string $to)
    {
        $settings = $this->getCompanySettings();
        $workOrders->loadMissing(['customer', 'items', 'assignments.technician', 'reports.technician', 'reports.photos']);

        return Pdf::loadView('pdf.vendor_invoice', [
            'vendor' => $vendor,
            'workOrders' => $workOrders,
            'from' => Carbon::parse($from),
            'to' => Carbon::parse($to),
            'total' => $workOrders->sum(fn($workOrder) => $workOrder->vendor_total),
            'settings' => $settings,
        ])->setPaper('a4', 'portrait');
    }

    public function generateCombinedInvoicePdf($workOrders)
    {
        $settings = $this->getCompanySettings();
        $workOrders->loadMissing(['customer', 'items', 'assignments.technician', 'reports.technician', 'reports.photos', 'invoice']);

        $subtotal = $workOrders->sum(function($workOrder) {
            return $workOrder->invoice ? $workOrder->invoice->subtotal : $workOrder->total;
        });

        $discount = $workOrders->sum(function($workOrder) {
            return $workOrder->invoice ? $workOrder->invoice->discount : 0;
        });

        $tax = $workOrders->sum(function($workOrder) {
            return $workOrder->invoice ? $workOrder->invoice->tax_amount : 0;
        });

        $total = $workOrders->sum(function($workOrder) {
            return $workOrder->invoice ? $workOrder->invoice->total : $workOrder->total;
        });

        return Pdf::loadView('pdf.combined_invoice', [
            'workOrders' => $workOrders,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'settings' => $settings,
        ])->setPaper('a4', 'portrait');
    }

    private function getCompanySettings(): array
    {
        return [
            'company_name' => Setting::get('company_name', 'PT. Azza Karunia Jaya'),
            'company_tagline' => Setting::get('company_tagline', 'Layanan AC dan elektronik terpercaya'),
            'company_address' => Setting::get('company_address', 'Alamat belum diatur'),
            'company_phone' => Setting::get('company_phone', '-'),
            'company_wa' => Setting::get('company_wa', '-'),
            'company_email' => Setting::get('company_email', '-'),
            'company_logo' => Setting::get('company_logo'),
            'payment_bank_name' => Setting::get('payment_bank_name', 'BCA'),
            'payment_bank_code' => Setting::get('payment_bank_code', '014'),
            'payment_account_number' => Setting::get('payment_account_number', '-'),
            'payment_account_holder' => Setting::get('payment_account_holder', 'PT. Azza Karunia Jaya'),
            'payment_instructions' => Setting::get('payment_instructions', ''),
            'invoice_footer' => Setting::get('invoice_footer', 'Terima kasih atas kepercayaan Anda.'),
            'invoice_terms' => Setting::get('invoice_terms', 'Pembayaran jatuh tempo sesuai tanggal yang tertera.'),
            'invoice_signer_name' => Setting::get('invoice_signer_name', ''),
            'invoice_signer_position' => Setting::get('invoice_signer_position', 'Kepala Operasional'),
        ];
    }

    private function getInvoiceV2Data(Invoice $invoice, array $settings, bool $isReceipt = false): array
    {
        $logo = $settings['company_logo'];
        if ($logo && ! str_starts_with($logo, 'data:') && ! filter_var($logo, FILTER_VALIDATE_URL)) {
            $fullPath = public_path(ltrim($logo, '/'));
            $logo = file_exists($fullPath) ? $fullPath : null;
        }

        $bankName = $invoice->financialAccount?->name ?: $settings['payment_bank_name'];
        $bankCode = $invoice->financialAccount?->code ?: $settings['payment_bank_code'];
        $accountNumber = !empty($settings['payment_account_number']) && $settings['payment_account_number'] !== '-'
            ? $settings['payment_account_number']
            : ($invoice->financialAccount?->code ?? '-');
        $accountHolder = $settings['payment_account_holder'] ?: $settings['company_name'];
        $signerName = !empty($settings['invoice_signer_name'])
            ? $settings['invoice_signer_name']
            : ($invoice->issuer?->name ?? $settings['company_name']);
        $signerPosition = $settings['invoice_signer_position'] ?: 'Penerbit Invoice';

        return [
            'documentTitle' => $isReceipt ? 'KWITANSI' : 'INVOICE',
            'documentStatus' => $isReceipt ? 'LUNAS' : null,
            'amountLabel' => $isReceipt ? 'TOTAL DIBAYAR' : 'JUMLAH YANG HARUS DIBAYAR',
            'dateLabel' => $isReceipt ? 'Tanggal Pembayaran' : 'Tanggal',
            'invoiceNumber' => $invoice->invoice_number,
            'invoiceDate' => ($isReceipt ? $invoice->payment_date : $invoice->created_at)?->format('d/m/Y') ?? '-',
            'dueDate' => $isReceipt ? '-' : ($invoice->due_date?->format('d/m/Y') ?? '-'),
            'company' => [
                'name' => $settings['company_name'],
                'tagline' => $settings['company_tagline'],
                'phone' => $settings['company_phone'],
                'wa' => $settings['company_wa'],
                'email' => $settings['company_email'],
                'address' => $settings['company_address'],
                'logo' => $logo,
            ],
            'client' => [
                'name' => $invoice->customer->display_name,
                'address' => $invoice->customer->address ?? '-',
                'phone' => $invoice->customer->phone ?? '-',
                'email' => $invoice->customer->email ?? '-',
            ],
            'items' => $invoice->items->map(fn($item) => [
                'name' => $item->description,
                'description' => $item->unit ? "Satuan: {$item->unit}" : null,
                'price' => (float) $item->unit_price,
                'qty' => $item->quantity,
            ])->all(),
            'subtotal' => (float) $invoice->subtotal,
            'discount' => (float) $invoice->discount,
            'discountLabel' => $invoice->discount_type === 'percent'
                ? intval($invoice->discount_value) . '%'
                : null,
            'tax' => (float) $invoice->tax_amount,
            'total' => $isReceipt ? (float) $invoice->paid_amount : (float) $invoice->total,
            'payment' => [
                'bank_code' => $bankCode,
                'bank_name' => $bankName,
                'account_number' => $accountNumber,
                'account_holder' => $accountHolder,
                'instructions' => $settings['payment_instructions'],
                'method' => $invoice->payment_method ?? ($isReceipt ? 'LUNAS' : '-'),
                'email' => $settings['company_email'],
            ],
            'signature' => [
                'name' => $signerName,
                'position' => $signerPosition,
                'image' => null,
            ],
            'terms' => $settings['invoice_terms'],
            'footer' => $settings['invoice_footer'],
        ];
    }
}
