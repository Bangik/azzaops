<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIncomeRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'category_id' => [
        'required',
        Rule::exists('financial_categories', 'id')->where('type', 'income')->where('is_active', true),
      ],
      'description' => ['required', 'string', 'max:255'],
      'amount' => ['required', 'numeric', 'min:1'],
      'transaction_date' => ['required', 'date'],
      'reference_number' => ['nullable', 'string', 'max:100'],
    ];
  }

  public function messages(): array
  {
    return [
      'category_id.required' => 'Kategori pemasukan wajib dipilih',
      'category_id.exists' => 'Kategori pemasukan tidak valid atau tidak aktif',
      'description.required' => 'Deskripsi pemasukan wajib diisi',
      'amount.required' => 'Jumlah nominal pemasukan wajib diisi',
      'amount.min' => 'Jumlah minimal adalah Rp 1',
      'transaction_date.required' => 'Tanggal pemasukan wajib diisi',
    ];
  }
}
