<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Rab;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfService
{
    public function generateInvoicePdf(Invoice $invoice)
    {
        $invoice->load(['customer', 'workOrder.assignments.technician', 'items', 'issuer']);
        $settings = $this->getCompanySettings();

        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf;
    }

    public function generateRabPdf(Rab $rab)
    {
        $rab->load(['customer', 'workOrder', 'items', 'creator']);
        $settings = $this->getCompanySettings();

        $pdf = Pdf::loadView('pdf.rab', compact('rab', 'settings'))
            ->setPaper('a4', 'portrait');

        return $pdf;
    }

    private function getCompanySettings(): array
    {
        return [
            'company_name' => Setting::get('company_name', 'PT. Azza Karunia Jaya'),
            'company_address' => Setting::get('company_address', 'Alamat belum diatur'),
            'company_phone' => Setting::get('company_phone', '-'),
            'company_wa' => Setting::get('company_wa', '-'),
            'company_email' => Setting::get('company_email', '-'),
            'invoice_footer' => Setting::get('invoice_footer', 'Terima kasih atas kepercayaan Anda.'),
        ];
    }
}
