<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RabStatus;
use App\Http\Controllers\Controller;
use App\Models\Rab;
use App\Models\WorkOrder;
use App\Services\RabService;
use App\Services\PdfService;
use Illuminate\Http\Request;

class RabController extends Controller
{
    public function __construct(
        private readonly RabService $rabService,
        private readonly PdfService $pdfService
    ) {}

    public function index(Request $request)
    {
        $query = Rab::with(['customer', 'workOrder'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($b) use ($q) {
                $b->where('rab_number', 'like', "%{$q}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }

        $rabs = $query->paginate(15)->withQueryString();

        return view('admin.rab.index', compact('rabs'));
    }

    public function create(Request $request)
    {
        $workOrder = WorkOrder::with(['customer', 'items'])->findOrFail($request->work_order_id);

        return view('admin.rab.create', compact('workOrder'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'work_order_id' => ['required', 'exists:work_orders,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'valid_until' => ['nullable', 'date'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.category' => ['required', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $rab = $this->rabService->create($data, $request->user()->id);

        return redirect()
            ->route('admin.rab.show', $rab)
            ->with('success', 'RAB berhasil dibuat');
    }

    public function show(Rab $rab)
    {
        $rab->load(['customer', 'workOrder', 'items', 'creator']);

        return view('admin.rab.show', compact('rab'));
    }

    public function send(Rab $rab)
    {
        $this->rabService->markSent($rab);

        return redirect()
            ->route('admin.rab.show', $rab)
            ->with('success', 'Status RAB diubah menjadi terkirim');
    }

    public function approve(Rab $rab)
    {
        $this->rabService->markApproved($rab);

        return redirect()
            ->route('admin.rab.show', $rab)
            ->with('success', 'RAB disetujui, status pekerjaan ter-update');
    }

    public function downloadPdf(Rab $rab)
    {
        $pdf = $this->pdfService->generateRabPdf($rab);

        return $pdf->download("rab-{$rab->rab_number}.pdf");
    }

    public function previewPdf(Rab $rab)
    {
        $pdf = $this->pdfService->generateRabPdf($rab);

        return $pdf->stream("rab-{$rab->rab_number}.pdf");
    }
}
