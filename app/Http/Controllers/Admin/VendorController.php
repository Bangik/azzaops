<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVendorRequest;
use App\Http\Requests\Admin\UpdateVendorRequest;
use App\Models\Vendor;

class VendorController extends Controller
{
  public function index()
  {
    $vendors = Vendor::withCount('workOrders')->orderBy('name')->paginate(15);
    return view('admin.vendors.index', compact('vendors'));
  }

  public function create()
  {
    return view('admin.vendors.create');
  }

  public function store(StoreVendorRequest $request)
  {
    Vendor::create([...$request->validated(), 'is_active' => $request->boolean('is_active')]);
    return redirect()->route('admin.vendors.index')->with('success', 'Vendor berhasil ditambahkan');
  }

  public function edit(Vendor $vendor)
  {
    return view('admin.vendors.edit', compact('vendor'));
  }

  public function update(UpdateVendorRequest $request, Vendor $vendor)
  {
    $vendor->update([...$request->validated(), 'is_active' => $request->boolean('is_active')]);
    return redirect()->route('admin.vendors.index')->with('success', 'Vendor berhasil diperbarui');
  }

  public function destroy(Vendor $vendor)
  {
    if ($vendor->workOrders()->exists()) {
      return redirect()->route('admin.vendors.index')->with('error', 'Vendor tidak dapat dihapus karena masih digunakan pada work order');
    }

    $vendor->delete();
    return redirect()->route('admin.vendors.index')->with('success', 'Vendor berhasil dihapus');
  }
}
