<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'name' => ['required', 'string', 'max:255'],
      'phone' => ['nullable', 'string', 'max:20'],
      'email' => ['nullable', 'email', 'max:255'],
      'address' => ['nullable', 'string'],
      'notes' => ['nullable', 'string'],
      'is_active' => ['nullable', 'boolean'],
    ];
  }
}
