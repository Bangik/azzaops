<?php

namespace App\Services;

use App\Enums\TransactionType;
use App\Models\Expense;
use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinanceService
{
    public function getSummary(string $from, string $to): array
    {
        $totalIncome = FinancialTransaction::income()
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $totalExpense = FinancialTransaction::expenseType()
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');

        $balance = $totalIncome - $totalExpense;

        $costPercentage = $totalIncome > 0
            ? round(($totalExpense / $totalIncome) * 100, 2)
            : 0;

        return [
            'total_income' => $totalIncome,
            'total_expense' => $totalExpense,
            'balance' => $balance,
            'cost_percentage' => $costPercentage,
        ];
    }

    public function createExpense(array $data, int $recordedBy): Expense
    {
        return DB::transaction(function () use ($data, $recordedBy) {
            $photoPath = null;
            if (isset($data['receipt_photo']) && $data['receipt_photo']->isValid()) {
                $file = $data['receipt_photo'];
                $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/receipts'), $filename);
                $photoPath = "uploads/receipts/{$filename}";
            }

            $expense = Expense::create([
                'category_id' => $data['category_id'],
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'work_order_id' => $data['work_order_id'] ?? null,
                'description' => $data['description'],
                'pic' => $data['pic'] ?? null,
                'amount' => $data['amount'],
                'expense_date' => $data['expense_date'],
                'receipt_photo' => $photoPath,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $recordedBy,
            ]);

            // Create transaction record
            FinancialTransaction::create([
                'type' => TransactionType::Expense,
                'category_id' => $data['category_id'],
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'expense_id' => $expense->id,
                'amount' => $data['amount'],
                'transaction_date' => $data['expense_date'],
                'description' => $data['description'],
                'recorded_by' => $recordedBy,
            ]);

            return $expense;
        });
    }

    public function createIncome(array $data, int $recordedBy): FinancialTransaction
    {
        return DB::transaction(fn() => FinancialTransaction::create([
            'type' => TransactionType::Income,
            'category_id' => $data['category_id'],
            'financial_account_id' => $data['financial_account_id'] ?? null,
            'amount' => $data['amount'],
            'transaction_date' => $data['transaction_date'],
            'description' => $data['description'],
            'reference_number' => $data['reference_number'] ?? null,
            'recorded_by' => $recordedBy,
        ]));
    }

    public function updateExpense(Expense $expense, array $data): Expense
    {
        return DB::transaction(function () use ($expense, $data) {
            $photoPath = $expense->receipt_photo;
            if (isset($data['receipt_photo']) && $data['receipt_photo']->isValid()) {
                // Delete old file if exists
                if ($expense->receipt_photo && file_exists(public_path($expense->receipt_photo))) {
                    @unlink(public_path($expense->receipt_photo));
                }
                $file = $data['receipt_photo'];
                $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/receipts'), $filename);
                $photoPath = "uploads/receipts/{$filename}";
            }

            $expense->update([
                'category_id' => $data['category_id'],
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'work_order_id' => $data['work_order_id'] ?? null,
                'description' => $data['description'],
                'pic' => $data['pic'] ?? null,
                'amount' => $data['amount'],
                'expense_date' => $data['expense_date'],
                'receipt_photo' => $photoPath,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update transaction record
            $expense->transaction()->update([
                'category_id' => $data['category_id'],
                'financial_account_id' => $data['financial_account_id'] ?? null,
                'amount' => $data['amount'],
                'transaction_date' => $data['expense_date'],
                'description' => $data['description'],
            ]);

            return $expense;
        });
    }

    public function deleteExpense(Expense $expense): void
    {
        DB::transaction(function () use ($expense) {
            if ($expense->receipt_photo && file_exists(public_path($expense->receipt_photo))) {
                @unlink(public_path($expense->receipt_photo));
            }
            $expense->transaction()?->delete();
            $expense->delete();
        });
    }
}
