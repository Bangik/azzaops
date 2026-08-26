<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFinancialCategoryRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255', 'unique:financial_categories,name'],
      'description' => ['nullable', 'string'],
      'is_active' => ['nullable', 'boolean'],
    ];
  }

  public function messages(): array
  {
    return [
      'name.required' => 'Nama kategori wajib diisi',
      'name.unique' => 'Nama kategori sudah ada',
    ];
  }
}
