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
        $query = WorkOrder::with(['customer', 'serviceCategory', 'assignments.technician'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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

        return view('admin.work-orders.index', compact('workOrders'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $categories = ServiceCategory::active()->orderBy('name')->get();

        return view('admin.work-orders.create', compact('customers', 'categories'));
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

        return view('admin.work-orders.edit', compact('workOrder', 'customers', 'categories'));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder)
    {
        if ($workOrder->status === WorkOrderStatus::Completed) {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Work order yang sudah selesai tidak dapat diedit');
        }

        $this->workOrderService->update($workOrder, $request->validated());

        return redirect()
            ->route('admin.work-orders.show', $workOrder)
            ->with('success', 'Work order berhasil diperbarui');
    }

    public function destroy(WorkOrder $workOrder)
    {
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
        if ($workOrder->type !== \App\Enums\WorkOrderType::Checking) {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Hanya work order pengecekan yang dapat dilanjutkan');
        }

        $categories = ServiceCategory::active()->orderBy('name')->get();

        return view('admin.work-orders.continue', compact('workOrder', 'categories'));
    }

    public function storeContinue(Request $request, WorkOrder $workOrder)
    {
        if ($workOrder->type !== \App\Enums\WorkOrderType::Checking) {
            return redirect()
                ->route('admin.work-orders.show', $workOrder)
                ->with('error', 'Hanya work order pengecekan yang dapat dilanjutkan');
        }

        $data = $request->validate([
            'type' => ['required', 'string', 'in:service,installation,maintenance'],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string'],
            'gmaps_link' => ['nullable', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
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
}
