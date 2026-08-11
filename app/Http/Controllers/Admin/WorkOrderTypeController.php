<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WorkOrderType;
use Illuminate\Http\Request;

class WorkOrderTypeController extends Controller
{
    public function index()
    {
        $types = WorkOrderType::orderBy('name')->get();
        return view('admin.work-order-types.index', compact('types'));
    }

    public function create()
    {
        return view('admin.work-order-types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:work_order_types,name'],
            'code' => ['required', 'string', 'max:50', 'unique:work_order_types,code'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        WorkOrderType::create($validated);

        return redirect()
            ->route('admin.work-order-types.index')
            ->with('success', 'Tipe pekerjaan berhasil dibuat');
    }

    public function edit(WorkOrderType $workOrderType)
    {
        return view('admin.work-order-types.edit', compact('workOrderType'));
    }

    public function update(Request $request, WorkOrderType $workOrderType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:work_order_types,name,' . $workOrderType->id],
            'code' => ['required', 'string', 'max:50', 'unique:work_order_types,code,' . $workOrderType->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $workOrderType->update($validated);

        return redirect()
            ->route('admin.work-order-types.index')
            ->with('success', 'Tipe pekerjaan berhasil diperbarui');
    }

    public function destroy(WorkOrderType $workOrderType)
    {
        if ($workOrderType->workOrders()->exists()) {
            return redirect()
                ->route('admin.work-order-types.index')
                ->with('error', 'Tipe pekerjaan tidak dapat dihapus karena masih digunakan oleh work order');
        }

        $workOrderType->delete();

        return redirect()
            ->route('admin.work-order-types.index')
            ->with('success', 'Tipe pekerjaan berhasil dihapus');
    }
}
