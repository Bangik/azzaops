<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AssignWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'technician_ids' => ['required', 'array', 'min:1'],
            'technician_ids.*' => ['required', 'exists:users,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'technician_ids.required' => 'Pilih minimal 1 teknisi',
            'technician_ids.min' => 'Pilih minimal 1 teknisi',
            'technician_ids.*.exists' => 'Teknisi tidak valid',
        ];
    }
}
