<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Rab;
use App\Models\Setting;
use App\Models\WorkOrder;
use Barryvdh\DomPDF\Facade\Pdf;

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

        $pdf = Pdf::loadView('pdf.report', compact('workOrder', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function generateInvoiceReportPdf(WorkOrder $workOrder)
    {
        $workOrder->load(['customer', 'serviceCategory', 'creator', 'assignments.technician', 'reports.technician', 'reports.photos', 'invoice.items', 'invoice.issuer']);
        $settings = $this->getCompanySettings();

        $pdf = Pdf::loadView('pdf.invoice_report', compact('workOrder', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf;
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
            'invoice_footer' => Setting::get('invoice_footer', 'Terima kasih atas kepercayaan Anda.'),
        ];
    }

    private function getInvoiceV2Data(Invoice $invoice, array $settings, bool $isReceipt = false): array
    {
        $logo = $settings['company_logo'];
        if ($logo && ! str_starts_with($logo, 'data:') && ! filter_var($logo, FILTER_VALIDATE_URL)) {
            $logo = public_path(ltrim($logo, '/'));
        }

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
                'bank_code' => $invoice->financialAccount?->code ?? '-',
                'bank_name' => $invoice->financialAccount?->name ?? 'Akun Keuangan',
                'account_number' => $invoice->financialAccount?->code ?? '-',
                'method' => $invoice->payment_method ?? '-',
                'email' => $settings['company_email'],
            ],
            'signature' => [
                'name' => $invoice->issuer?->name ?? $settings['company_name'],
                'position' => 'Penerbit Invoice',
                'image' => null,
            ],
        ];
    }
}
