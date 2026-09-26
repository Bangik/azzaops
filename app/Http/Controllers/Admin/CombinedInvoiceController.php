<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use App\Models\Customer;
use App\Services\PdfService;
use Illuminate\Http\Request;

class CombinedInvoiceController extends Controller
{
    public function create(Request $request)
    {
        $query = WorkOrder::with('customer')->orderBy('created_at', 'desc');

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('scheduled_date', [$request->from, $request->to]);
        }

        $workOrders = $query->paginate(50)->withQueryString();
        $customers = Customer::orderBy('name')->get();

        return view('admin.combined-invoices.create', compact('workOrders', 'customers'));
    }

    public function download(Request $request, PdfService $pdfService)
    {
        $request->validate([
            'work_order_ids' => 'required|array|min:1',
            'work_order_ids.*' => 'exists:work_orders,id',
        ]);

        $workOrders = WorkOrder::with([
            'customer',
            'items',
            'assignments.technician',
            'reports.technician',
            'reports.photos',
            'invoice' // if we need invoice amounts
        ])->whereIn('id', $request->work_order_ids)
          ->orderBy('scheduled_date')
          ->get();

        $pdf = $pdfService->generateCombinedInvoicePdf($workOrders);
        $filename = 'invoice-gabungan-' . now()->format('YmdHis') . '.pdf';

        return $pdf->download($filename);
    }
}
