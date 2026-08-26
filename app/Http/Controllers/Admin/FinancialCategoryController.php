<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialCategoryRequest;
use App\Http\Requests\Admin\UpdateFinancialCategoryRequest;
use App\Models\FinancialCategory;
use App\Enums\TransactionType;

class FinancialCategoryController extends Controller
{
  public function index()
  {
    $categories = FinancialCategory::expense()->orderBy('name')->paginate(15);

    return view('admin.financial-categories.index', compact('categories'));
  }

  public function create()
  {
    return view('admin.financial-categories.create');
  }

  public function store(StoreFinancialCategoryRequest $request)
  {
    FinancialCategory::create([
      ...$request->validated(),
      'type' => TransactionType::Expense,
      'is_active' => $request->boolean('is_active'),
    ]);

    return redirect()
      ->route('admin.financial-categories.index')
      ->with('success', 'Kategori pengeluaran berhasil ditambahkan');
  }

  public function edit(FinancialCategory $financialCategory)
  {
    abort_unless($financialCategory->type === TransactionType::Expense, 404);

    return view('admin.financial-categories.edit', compact('financialCategory'));
  }

  public function update(UpdateFinancialCategoryRequest $request, FinancialCategory $financialCategory)
  {
    abort_unless($financialCategory->type === TransactionType::Expense, 404);

    $financialCategory->update([
      ...$request->validated(),
      'is_active' => $request->boolean('is_active'),
    ]);

    return redirect()
      ->route('admin.financial-categories.index')
      ->with('success', 'Kategori pengeluaran berhasil diperbarui');
  }

  public function destroy(FinancialCategory $financialCategory)
  {
    abort_unless($financialCategory->type === TransactionType::Expense, 404);

    if ($financialCategory->expenses()->exists() || $financialCategory->transactions()->exists()) {
      return redirect()
        ->route('admin.financial-categories.index')
        ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan');
    }

    $financialCategory->delete();

    return redirect()
      ->route('admin.financial-categories.index')
      ->with('success', 'Kategori pengeluaran berhasil dihapus');
  }
}
