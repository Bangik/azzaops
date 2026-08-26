<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExpenseRequest;
use App\Http\Requests\Admin\StoreIncomeRequest;
use App\Http\Requests\Admin\UpdateExpenseRequest;
use App\Models\Expense;
use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Models\WorkOrder;
use App\Services\FinanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(
        private readonly FinanceService $financeService
    ) {}

    public function index(Request $request)
    {
        [$from, $to] = $this->resolvePeriod($request);

        $summary = $this->financeService->getSummary($from, $to);

        $transactions = FinancialTransaction::with(['category', 'invoice', 'expense', 'recorder'])
            ->whereBetween('transaction_date', [$from, $to])
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $expenses = Expense::with(['category', 'workOrder'])
            ->whereBetween('expense_date', [$from, $to])
            ->latest('expense_date')
            ->paginate(10, ['*'], 'expenses_page')
            ->withQueryString();

        return view('admin.finance.index', compact('summary', 'transactions', 'expenses', 'from', 'to'));
    }

    public function create()
    {
        $categories = FinancialCategory::expense()->active()->orderBy('name')->get();
        $workOrders = WorkOrder::latest()->limit(100)->get(['id', 'wo_number', 'title']);

        return view('admin.finance.create', compact('categories', 'workOrders'));
    }

    public function createIncome()
    {
        $categories = FinancialCategory::income()->active()->orderBy('name')->get();

        return view('admin.finance.income-create', compact('categories'));
    }

    public function storeIncome(StoreIncomeRequest $request)
    {
        $this->financeService->createIncome($request->validated(), $request->user()->id);

        return redirect()
            ->route('admin.finance.index')
            ->with('success', 'Pemasukan berhasil dicatat');
    }

    public function store(StoreExpenseRequest $request)
    {
        $this->financeService->createExpense($request->validated(), $request->user()->id);

        return redirect()
            ->route('admin.finance.index')
            ->with('success', 'Pengeluaran berhasil dicatat');
    }

    public function edit(Expense $expense)
    {
        $categories = FinancialCategory::expense()->active()->orderBy('name')->get();
        $workOrders = WorkOrder::latest()->limit(100)->get(['id', 'wo_number', 'title']);

        return view('admin.finance.edit', compact('expense', 'categories', 'workOrders'));
    }

    public function update(UpdateExpenseRequest $request, Expense $expense)
    {
        $this->financeService->updateExpense($expense, $request->validated());

        return redirect()
            ->route('admin.finance.index')
            ->with('success', 'Pengeluaran berhasil diperbarui');
    }

    public function destroy(Expense $expense)
    {
        $this->financeService->deleteExpense($expense);

        return redirect()
            ->route('admin.finance.index')
            ->with('success', 'Pengeluaran berhasil dihapus');
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->get('period', 'month');

        return match ($period) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'custom' => [
                $request->get('from', now()->startOfMonth()->toDateString()),
                $request->get('to', now()->toDateString()),
            ],
            default => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
        };
    }
}
