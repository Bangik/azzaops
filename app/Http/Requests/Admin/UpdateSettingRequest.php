<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [UserRole::SuperAdmin, UserRole::Admin], true);
    }

    public function rules(): array
    {
        return [
            // Identitas & Header
            'company_name' => ['required', 'string', 'max:255'],
            'company_tagline' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'company_phone' => ['nullable', 'string', 'max:50'],
            'company_wa' => ['nullable', 'string', 'max:50'],
            'company_email' => ['nullable', 'email', 'max:100'],
            'company_logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,svg', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],

            // Metode Pembayaran
            'payment_bank_name' => ['nullable', 'string', 'max:100'],
            'payment_bank_code' => ['nullable', 'string', 'max:50'],
            'payment_account_number' => ['nullable', 'string', 'max:100'],
            'payment_account_holder' => ['nullable', 'string', 'max:150'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],

            // Pengaturan Dokumen & Penandatangan
            'invoice_prefix' => ['nullable', 'string', 'max:20'],
            'wo_prefix' => ['nullable', 'string', 'max:20'],
            'rab_prefix' => ['nullable', 'string', 'max:20'],
            'tax_default' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'invoice_signer_name' => ['nullable', 'string', 'max:150'],
            'invoice_signer_position' => ['nullable', 'string', 'max:150'],
            'invoice_footer' => ['nullable', 'string', 'max:255'],
            'invoice_terms' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'company_email.email' => 'Format email perusahaan tidak valid.',
            'company_logo.image' => 'File logo harus berupa gambar.',
            'company_logo.mimes' => 'Logo harus berformat jpeg, png, jpg, webp, atau svg.',
            'company_logo.max' => 'Ukuran logo maksimal 5 MB.',
        ];
    }
}
