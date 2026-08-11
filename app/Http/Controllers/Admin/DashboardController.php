<?php

namespace App\Http\Controllers\Admin;

use App\Enums\TransactionType;
use App\Enums\WorkOrderStatus;
use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Stat cards
        $woNew = WorkOrder::status(WorkOrderStatus::Pending)->count() + WorkOrder::status(WorkOrderStatus::Assigned)->count();
        $woInProgress = WorkOrder::status(WorkOrderStatus::InProgress)->count() + WorkOrder::status(WorkOrderStatus::Checking)->count();
        $woCompleted = WorkOrder::status(WorkOrderStatus::Completed)->count();
        $incomeThisMonth = FinancialTransaction::income()
            ->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year)
            ->sum('amount');

        // 2. Finance trend data (last 6 months)
        $months = [];
        $incomeData = [];
        $expenseData = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->translatedFormat('F Y');

            $incomeData[] = (float) FinancialTransaction::income()
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');

            $expenseData[] = (float) FinancialTransaction::expenseType()
                ->whereMonth('transaction_date', $date->month)
                ->whereYear('transaction_date', $date->year)
                ->sum('amount');
        }

        // 3. Work Order types distribution (current month)
        $typeLabels = [];
        $typeValues = [];
        $dbTypes = \App\Models\WorkOrderType::orderBy('name')->get();
        foreach ($dbTypes as $type) {
            $typeLabels[] = $type->name;
            $typeValues[] = WorkOrder::where('work_order_type_id', $type->id)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();
        }

        // 4. Work Orders requiring attention (pending / reported)
        $recentActions = WorkOrder::with(['customer', 'serviceCategory', 'type'])
            ->whereIn('status', [WorkOrderStatus::Pending, WorkOrderStatus::Reported])
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard.index', compact(
            'woNew',
            'woInProgress',
            'woCompleted',
            'incomeThisMonth',
            'months',
            'incomeData',
            'expenseData',
            'typeLabels',
            'typeValues',
            'recentActions'
        ));
    }
}
