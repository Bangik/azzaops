<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFinancialCategoryRequest;
use App\Http\Requests\Admin\UpdateFinancialCategoryRequest;
use App\Models\FinancialCategory;
use App\Enums\TransactionType;
use Illuminate\Http\Request;

class FinancialCategoryController extends Controller
{
  public function index(Request $request)
  {
    $type = $this->categoryType($request);
    $categories = FinancialCategory::where('type', $type)->orderBy('name')->paginate(15);

    return view('admin.financial-categories.index', [
      'categories' => $categories,
      'categoryLabel' => $this->categoryLabel($type),
      'routePrefix' => $this->routePrefix($request),
    ]);
  }

  public function create(Request $request)
  {
    $type = $this->categoryType($request);

    return view('admin.financial-categories.create', [
      'categoryLabel' => $this->categoryLabel($type),
      'routePrefix' => $this->routePrefix($request),
    ]);
  }

  public function store(StoreFinancialCategoryRequest $request)
  {
    $type = $this->categoryType($request);

    FinancialCategory::create([
      ...$request->validated(),
      'type' => $type,
      'is_active' => $request->boolean('is_active'),
    ]);

    return redirect()
      ->route("admin.{$this->routePrefix($request)}.index")
      ->with('success', "Kategori {$this->categoryLabel($type)} berhasil ditambahkan");
  }

  public function edit(Request $request, FinancialCategory $financialCategory)
  {
    $type = $this->categoryType($request);
    abort_unless($financialCategory->type === $type, 404);

    return view('admin.financial-categories.edit', [
      'financialCategory' => $financialCategory,
      'categoryLabel' => $this->categoryLabel($type),
      'routePrefix' => $this->routePrefix($request),
    ]);
  }

  public function update(UpdateFinancialCategoryRequest $request, FinancialCategory $financialCategory)
  {
    $type = $this->categoryType($request);
    abort_unless($financialCategory->type === $type, 404);

    $financialCategory->update([
      ...$request->validated(),
      'is_active' => $request->boolean('is_active'),
    ]);

    return redirect()
      ->route("admin.{$this->routePrefix($request)}.index")
      ->with('success', "Kategori {$this->categoryLabel($type)} berhasil diperbarui");
  }

  public function destroy(Request $request, FinancialCategory $financialCategory)
  {
    $type = $this->categoryType($request);
    abort_unless($financialCategory->type === $type, 404);

    if ($financialCategory->expenses()->exists() || $financialCategory->transactions()->exists()) {
      return redirect()
        ->route("admin.{$this->routePrefix($request)}.index")
        ->with('error', 'Kategori tidak dapat dihapus karena masih digunakan');
    }

    $financialCategory->delete();

    return redirect()
      ->route("admin.{$this->routePrefix($request)}.index")
      ->with('success', "Kategori {$this->categoryLabel($type)} berhasil dihapus");
  }

  private function categoryType(Request $request): TransactionType
  {
    return $request->routeIs('admin.income-categories.*')
      ? TransactionType::Income
      : TransactionType::Expense;
  }

  private function routePrefix(Request $request): string
  {
    return $this->categoryType($request) === TransactionType::Income
      ? 'income-categories'
      : 'financial-categories';
  }

  private function categoryLabel(TransactionType $type): string
  {
    return $type === TransactionType::Income ? 'pemasukan' : 'pengeluaran';
  }
}
