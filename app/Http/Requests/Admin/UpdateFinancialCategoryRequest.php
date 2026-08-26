<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFinancialCategoryRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    $category = $this->route('financial_category');

    return [
      'name' => ['required', 'string', 'max:255', Rule::unique('financial_categories', 'name')->ignore($category->id)],
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
