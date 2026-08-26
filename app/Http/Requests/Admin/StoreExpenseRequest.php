<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:financial_categories,id'],
            'financial_account_id' => [
                'nullable',
                Rule::exists('financial_accounts', 'id')->where('is_active', true),
            ],
            'work_order_id' => ['nullable', 'exists:work_orders,id'],
            'description' => ['required', 'string', 'max:255'],
            'pic' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'receipt_photo' => ['nullable', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Kategori pengeluaran wajib dipilih',
            'description.required' => 'Deskripsi pengeluaran wajib diisi',
            'amount.required' => 'Jumlah nominal pengeluaran wajib diisi',
            'amount.min' => 'Jumlah minimal adalah Rp 1',
            'expense_date.required' => 'Tanggal pengeluaran wajib diisi',
        ];
    }
}
