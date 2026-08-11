<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialAccount;
use Illuminate\Http\Request;

class FinancialAccountController extends Controller
{
    public function index()
    {
        $accounts = FinancialAccount::orderBy('name')->get();
        return view('admin.financial-accounts.index', compact('accounts'));
    }

    public function create()
    {
        return view('admin.financial-accounts.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:financial_accounts,name'],
            'code' => ['required', 'string', 'max:50', 'unique:financial_accounts,code'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        FinancialAccount::create($validated);

        return redirect()
            ->route('admin.financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil dibuat');
    }

    public function edit(FinancialAccount $financialAccount)
    {
        return view('admin.financial-accounts.edit', compact('financialAccount'));
    }

    public function update(Request $request, FinancialAccount $financialAccount)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:financial_accounts,name,' . $financialAccount->id],
            'code' => ['required', 'string', 'max:50', 'unique:financial_accounts,code,' . $financialAccount->id],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->has('is_active');

        $financialAccount->update($validated);

        return redirect()
            ->route('admin.financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil diperbarui');
    }

    public function destroy(FinancialAccount $financialAccount)
    {
        if ($financialAccount->invoices()->exists()) {
            return redirect()
                ->route('admin.financial-accounts.index')
                ->with('error', 'Akun keuangan tidak dapat dihapus karena masih digunakan pada invoice');
        }

        $financialAccount->delete();

        return redirect()
            ->route('admin.financial-accounts.index')
            ->with('success', 'Akun keuangan berhasil dihapus');
    }
}
