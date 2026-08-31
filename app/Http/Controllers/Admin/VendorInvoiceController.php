<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\VendorInvoiceRequest;
use App\Models\Vendor;
use App\Models\WorkOrder;
use App\Services\PdfService;

class VendorInvoiceController extends Controller
{
  public function create()
  {
    $vendors = Vendor::active()->orderBy('name')->get();
    return view('admin.vendor-invoices.create', compact('vendors'));
  }

  public function download(VendorInvoiceRequest $request, PdfService $pdfService)
  {
    $vendor = Vendor::findOrFail($request->validated('vendor_id'));
    $workOrders = WorkOrder::with([
      'customer',
      'items',
      'assignments.technician',
      'reports.technician',
      'reports.photos',
    ])->where('vendor_id', $vendor->id)
      ->whereBetween('scheduled_date', [$request->validated('from'), $request->validated('to')])
      ->orderBy('scheduled_date')
      ->orderBy('job_order')
      ->get();

    if ($workOrders->isEmpty()) {
      return back()->withInput()->with('error', 'Tidak ada Work Order vendor pada periode tersebut');
    }

    $pdf = $pdfService->generateVendorInvoicePdf($vendor, $workOrders, $request->validated('from'), $request->validated('to'));
    $filename = 'invoice-vendor-' . str($vendor->name)->slug() . '-' . $request->validated('from') . '-sampai-' . $request->validated('to') . '.pdf';

    return $pdf->download($filename);
  }
}
