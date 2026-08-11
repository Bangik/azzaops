<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignWorkOrderRequest;
use App\Http\Requests\Admin\StoreWorkOrderRequest;
use App\Http\Requests\Admin\UpdateWorkOrderRequest;
use App\Models\Customer;
use App\Models\ServiceCategory;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Http\Request;

class WorkOrderController extends Controller
{
    public function __construct(
        private readonly WorkOrderService $workOrderService
    ) {}

    public function index(Request $request)
    {
        $query = WorkOrder::with(['customer', 'serviceCategory', 'type', 'assignments.technician'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('work_order_type_id')) {
            $query->where('work_order_type_id', $request->work_order_type_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($builder) use ($q) {
                $builder->where('wo_number', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")
                        ->orWhere('company_name', 'like', "%{$q}%"));
            });
        }

        $workOrders = $query->paginate(15)->withQueryString();
        $types = \App\Models\WorkOrderType::active()->orderBy('name')->get();

        return view('admin.work-orders.index', compact('workOrders', 'types'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $categories = ServiceCategory::active()->orderBy('name')->get();
        $types = \App\Models\WorkOrderType::active()->orderBy('name')->get();

        return view('admin.work-orders.create', compact('customers', 'categories', 'types'));
    }

    public function store(StoreWorkOrderRequest $request)
    {
        $workOrder = $this->workOrderService->create(
            $request->validated(),
            $request->user()->id
        );

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order berhasil dibuat');
    }

    public function show(WorkOrder $workOrder)
    {
        $workOrder->load([
            'customer',
            'serviceCategory',
            'creator',
            'items',
            'assignments.technician',
            'assignments.assigner',
            'reports.photos',
            'invoice',
            'rab',
            'parentWorkOrder',
        ]);

        $technicians = User::technicians()->orderBy('name')->get();

        return view('admin.work-orders.show', compact('workOrder', 'technicians'));
    }

    public function edit(WorkOrder $workOrder)
    {
        if ($workOrder->status === WorkOrderStatus::Completed) {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Work order yang sudah selesai tidak dapat diedit');
        }

        $workOrder->load('items');
        $customers = Customer::orderBy('name')->get();
        $categories = ServiceCategory::active()->orderBy('name')->get();
        $types = \App\Models\WorkOrderType::active()->orderBy('name')->get();

        return view('admin.work-orders.edit', compact('workOrder', 'customers', 'categories', 'types'));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder)
    {
        if ($workOrder->status === WorkOrderStatus::Completed) {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Work order yang sudah selesai tidak dapat diedit');
        }

        $validated = $request->validated();

        // admin cannot edit customer, ensure customer_id is not changed
        if ($request->user()->role->value === 'admin') {
            $validated['customer_id'] = $workOrder->customer_id;
        }

        $this->workOrderService->update($workOrder, $validated);

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order berhasil diperbarui');
    }

    public function destroy(WorkOrder $workOrder)
    {
        $user = request()->user();
        if ($user->role->value === 'admin') {
            abort(403, 'Admin tidak memiliki akses untuk membatalkan atau menghapus Work Order.');
        }

        // if super_admin and user specifically requested absolute delete
        if ($user->role->value === 'super_admin' && request()->get('action') === 'delete') {
            \Illuminate\Support\Facades\DB::transaction(function () use ($workOrder) {
                // Delete related invoices and their items/transactions
                if ($workOrder->invoice) {
                    $workOrder->invoice->transactions()->delete();
                    $workOrder->invoice->items()->delete();
                    $workOrder->invoice->delete();
                }

                // Delete related RAB and its items
                if ($workOrder->rab) {
                    $workOrder->rab->items()->delete();
                    $workOrder->rab->delete();
                }

                // Delete related assignments
                $workOrder->assignments()->delete();

                // Delete related reports and their photos
                foreach ($workOrder->reports as $report) {
                    $report->photos()->delete();
                    $report->delete();
                }

                // Delete related takeovers
                $workOrder->takeovers()->delete();

                // Delete items
                $workOrder->items()->delete();

                // Nullify parent_wo_id on child work orders to avoid breaking self-referential FK
                $workOrder->childWorkOrders()->update(['parent_wo_id' => null]);

                $workOrder->delete();
            });

            return redirect()
                ->route('admin.work-orders.index')
                ->with('success', 'Work order berhasil dihapus secara permanen.');
        }

        if (in_array($workOrder->status, [WorkOrderStatus::Completed, WorkOrderStatus::Cancelled], true)) {
            return redirect()
                ->route('admin.work-orders.index')
                ->with('error', 'Work order ini tidak dapat dibatalkan');
        }

        $workOrder->update(['status' => WorkOrderStatus::Cancelled]);

        return redirect()
            ->route('admin.work-orders.index')
            ->with('success', 'Work order berhasil dibatalkan');
    }

    public function assign(AssignWorkOrderRequest $request, WorkOrder $workOrder)
    {
        $user = $request->user();
        if (! in_array($user->role, [UserRole::SuperAdmin, UserRole::Admin, UserRole::KepalaTeknisi], true)) {
            abort(403, 'Anda tidak memiliki akses untuk menugaskan teknisi');
        }

        if (in_array($workOrder->status, [WorkOrderStatus::Completed, WorkOrderStatus::Cancelled], true)) {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Work order ini tidak dapat ditugaskan');
        }

        $this->workOrderService->assign(
            $workOrder,
            $request->validated('technician_ids'),
            $user->id
        );

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Teknisi berhasil ditugaskan');
    }

    public function continue(WorkOrder $workOrder)
    {
        if ($workOrder->type->code !== 'checking') {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Hanya work order pengecekan yang dapat dilanjutkan');
        }

        $categories = ServiceCategory::active()->orderBy('name')->get();
        $types = \App\Models\WorkOrderType::active()->where('code', '!=', 'checking')->orderBy('name')->get();

        return view('admin.work-orders.continue', compact('workOrder', 'categories', 'types'));
    }

    public function storeContinue(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->type->code !== 'checking') {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Hanya work order pengecekan yang dapat dilanjutkan');
        }

        $data = $request->validate([
            'work_order_type_id' => ['required', 'exists:work_order_types,id'],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string'],
            'gmaps_link' => ['nullable', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ]);

        $newWorkOrder = $this->workOrderService->continueFromChecking($workOrder, $data, $request->user()->id);

        return redirect()
            ->route('admin.work-orders.show', $newWorkOrder)
            ->with('success', 'Work order lanjutan berhasil dibuat. Biaya pengecekan sebelumnya diubah menjadi Rp 0 (Gratis)');
    }

    public function updateReport(Request $request, WorkOrder $workOrder, \App\Models\WorkOrderReport $report)
    {
        $data = $request->validate([
            'findings' => ['required', 'string'],
            'work_done' => ['required', 'string'],
            'recommendations' => ['nullable', 'string'],
        ]);

        $report->update($data);

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Laporan teknisi berhasil diperbarui');
    }

    public function downloadReportPdf(WorkOrder $workOrder, \App\Services\PdfService $pdfService)
    {
        $workOrder->load(['customer', 'reports.technician', 'reports.photos']);
        if ($workOrder->reports->isEmpty()) {
            return redirect()->back()->with('error', 'Belum ada laporan dari teknisi');
        }
        $pdf = $pdfService->generateReportPdf($workOrder);
        return $pdf->download("laporan-wo-{$workOrder->wo_number}.pdf");
    }

    public function downloadInvoiceReportPdf(WorkOrder $workOrder, \App\Services\PdfService $pdfService)
    {
        $workOrder->load(['customer', 'invoice', 'reports.technician', 'reports.photos']);
        if (!$workOrder->invoice) {
            return redirect()->back()->with('error', 'Invoice untuk Work Order ini belum dibuat');
        }
        if ($workOrder->reports->isEmpty()) {
            return redirect()->back()->with('error', 'Laporan pekerjaan dari teknisi belum dibuat');
        }
        $pdf = $pdfService->generateInvoiceReportPdf($workOrder);
        return $pdf->download("invoice-laporan-wo-{$workOrder->wo_number}.pdf");
    }
}
