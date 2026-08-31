<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VendorInvoiceRequest extends FormRequest
{
  public function authorize(): bool
  {
    return true;
  }

  public function rules(): array
  {
    return [
      'vendor_id' => ['required', 'exists:vendors,id'],
      'from' => ['required', 'date'],
      'to' => ['required', 'date', 'after_or_equal:from'],
    ];
  }
}
