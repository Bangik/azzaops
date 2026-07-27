<?php

namespace App\Http\Requests\Admin;

use App\Enums\CustomerType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(CustomerType::class)],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['required_if:type,' . CustomerType::Business->value, 'nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:255'],
            'market' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe pelanggan wajib diisi',
            'name.required' => 'Nama wajib diisi',
            'company_name.required_if' => 'Nama perusahaan wajib diisi untuk tipe perusahaan',
            'phone.required' => 'Nomor telepon wajib diisi',
            'email.email' => 'Format email tidak valid',
        ];
    }
}
