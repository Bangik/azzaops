<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderType;
use App\Services\PdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class DynamicInvoiceReceiptSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_settings_page(): void
    {
        $superAdmin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.settings.index'));

        $response->assertStatus(200);
        $response->assertSee('Identitas & Header Dokumen', false);
        $response->assertSee('Detail Metode Pembayaran', false);
    }

    public function test_admin_cannot_access_settings_page(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.settings.index'));

        $response->assertStatus(403);
    }

    public function test_super_admin_can_update_settings_dynamically_with_logo_and_payment(): void
    {
        $superAdmin = User::factory()->create([
            'role' => UserRole::SuperAdmin,
        ]);

        $logoFile = UploadedFile::fake()->image('custom_logo.png', 200, 80);

        $response = $this->actingAs($superAdmin)->put(route('admin.settings.update'), [
            'company_name' => 'PT Solusi Sejuk Abadi',
            'company_tagline' => 'Spesialis AC Modern',
            'company_address' => 'Jl. Merdeka No. 45, Jakarta Barat',
            'company_phone' => '021-888999',
            'company_wa' => '081299887766',
            'company_email' => 'finance@sejukabadi.com',
            'company_logo' => $logoFile,
            'payment_bank_name' => 'Bank Mandiri',
            'payment_bank_code' => '008',
            'payment_account_number' => '9876543210',
            'payment_account_holder' => 'PT Solusi Sejuk Abadi',
            'payment_instructions' => 'Sertakan nomor invoice pada berita transfer.',
            'invoice_signer_name' => 'Hendra Setiawan',
            'invoice_signer_position' => 'Direktur Utama',
            'invoice_footer' => 'Terima kasih atas kerja samanya.',
            'invoice_terms' => 'Garansi servis 30 hari kalender.',
        ]);

        $response->assertRedirect(route('admin.settings.index'));
        $response->assertSessionHas('success');

        $this->assertEquals('PT Solusi Sejuk Abadi', Setting::get('company_name'));
        $this->assertEquals('Spesialis AC Modern', Setting::get('company_tagline'));
        $this->assertEquals('Bank Mandiri', Setting::get('payment_bank_name'));
        $this->assertEquals('008', Setting::get('payment_bank_code'));
        $this->assertEquals('9876543210', Setting::get('payment_account_number'));
        $this->assertEquals('PT Solusi Sejuk Abadi', Setting::get('payment_account_holder'));
        $this->assertEquals('Hendra Setiawan', Setting::get('invoice_signer_name'));

        $logoPath = Setting::get('company_logo');
        $this->assertNotNull($logoPath);
        $this->assertFileExists(public_path($logoPath));

        // Clean up test file
        if (file_exists(public_path($logoPath))) {
            @unlink(public_path($logoPath));
        }
    }

    public function test_pdf_service_uses_dynamic_settings_for_invoice_and_receipt(): void
    {
        Setting::set('company_name', 'PT Sejuk Makmur Dinamis');
        Setting::set('company_tagline', 'Ahli Tata Udara');
        Setting::set('company_phone', '021-5551234');
        Setting::set('company_wa', '081234567890');
        Setting::set('company_email', 'billing@sejukmakmur.com');
        Setting::set('payment_bank_name', 'Bank BCA');
        Setting::set('payment_bank_code', '014');
        Setting::set('payment_account_number', '1122334455');
        Setting::set('payment_account_holder', 'PT Sejuk Makmur Dinamis');
        Setting::set('payment_instructions', 'Kirim bukti ke WhatsApp 081234567890');
        Setting::set('invoice_signer_name', 'Budi Santoso');
        Setting::set('invoice_signer_position', 'Manajer Keuangan');
        Setting::set('invoice_footer', 'TERIMA KASIH ATAS KUNJUNGAN ANDA');
        Setting::set('invoice_terms', 'Pembayaran maksimal 7 hari sejak tanggal terbit.');

        $customer = Customer::create([
            'name' => 'Bapak Arya',
            'phone' => '0811223344',
            'address' => 'Jl. Thamrin No. 10',
            'type' => 'individual',
        ]);

        $type = WorkOrderType::create([
            'name' => 'Perbaikan',
            'code' => 'PBK',
            'is_active' => true,
        ]);

        $category = \App\Models\ServiceCategory::create([
            'name' => 'AC Split',
            'is_active' => true,
        ]);

        $user = User::factory()->create();

        $workOrder = WorkOrder::create([
            'customer_id' => $customer->id,
            'work_order_type_id' => $type->id,
            'service_category_id' => $category->id,
            'wo_number' => 'WO-TEST-001',
            'title' => 'Servis AC Kantor',
            'location' => 'Jakarta Selatan',
            'status' => WorkOrderStatus::Completed,
            'description' => 'Servis AC Kantor',
            'scheduled_date' => now(),
            'created_by' => $user->id,
        ]);

        $invoice = Invoice::create([
            'work_order_id' => $workOrder->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-001',
            'subtotal' => 500000,
            'discount' => 0,
            'tax_amount' => 0,
            'total' => 500000,
            'paid_amount' => 500000,
            'payment_status' => PaymentStatus::Paid,
            'status' => InvoiceStatus::Paid,
            'payment_date' => now(),
            'payment_method' => 'Transfer',
            'issued_by' => $user->id,
        ]);

        InvoiceItem::create([
            'invoice_id' => $invoice->id,
            'description' => 'Servis AC Split 1 PK',
            'quantity' => 2,
            'unit' => 'unit',
            'unit_price' => 250000,
            'total_price' => 500000,
        ]);

        $pdfService = app(PdfService::class);

        // Generate Invoice PDF
        $invoicePdf = $pdfService->generateInvoicePdf($invoice);
        $invoiceOutput = $invoicePdf->output();
        $this->assertNotEmpty($invoiceOutput);

        // Generate Receipt PDF
        $receiptPdf = $pdfService->generateReceiptPdf($invoice);
        $receiptOutput = $receiptPdf->output();
        $this->assertNotEmpty($receiptOutput);
    }
}
