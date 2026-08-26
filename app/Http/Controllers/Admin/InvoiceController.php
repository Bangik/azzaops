<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Services\InvoiceService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly PdfService $pdfService
    ) {}

    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'workOrder'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('invoice_number', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        return view('admin.invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $workOrder = WorkOrder::with(['customer', 'items'])->findOrFail($request->work_order_id);

        return view('admin.invoices.create', compact('workOrder'));
    }

    public function store(Request $request)
    {
        $workOrder = WorkOrder::findOrFail($request->work_order_id);

        $data = $request->validate([
            'due_date' => ['nullable', 'date'],
            'discount_type' => ['required', 'string', 'in:percent,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $invoice = $this->invoiceService->createFromWorkOrder($workOrder, $data, $request->user()->id);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Invoice berhasil digenerate');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'workOrder', 'items', 'issuer']);

        return view('admin.invoices.show', compact('invoice'));
    }

    public function pay(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'paid_amount' => ['required', 'numeric', 'min:1', 'max:' . $invoice->total],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'string', 'max:100'],
            'financial_account_id' => ['required', 'exists:financial_accounts,id'],
        ]);

        $this->invoiceService->markPaid($invoice, $data, $request->user()->id);

        return redirect()
            ->route('admin.invoices.show', $invoice)
            ->with('success', 'Pembayaran berhasil dicatat');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $pdf = $this->pdfService->generateInvoicePdf($invoice);

        return $pdf->download("invoice-{$invoice->invoice_number}.pdf");
    }

    public function previewPdf(Invoice $invoice)
    {
        $pdf = $this->pdfService->generateInvoicePdf($invoice);

        return $pdf->stream("invoice-{$invoice->invoice_number}.pdf");
    }

    public function receiptPdf(Invoice $invoice)
    {
        if ($invoice->payment_status !== PaymentStatus::Paid) {
            return redirect()->back()->with('error', 'Kwitansi hanya dapat dibuat setelah invoice lunas');
        }

        $pdf = $this->pdfService->generateReceiptPdf($invoice);

        return $pdf->download("kwitansi-{$invoice->invoice_number}.pdf");
    }
}
