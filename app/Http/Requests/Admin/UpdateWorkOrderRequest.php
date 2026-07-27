<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', 'string', 'in:checking,service,installation,maintenance'],
            'customer_id' => ['required', 'exists:customers,id'],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'priority' => ['required', 'string', 'in:low,normal,high,urgent'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Tipe pekerjaan wajib dipilih',
            'customer_id.required' => 'Customer wajib dipilih',
            'service_category_id.required' => 'Kategori layanan wajib dipilih',
            'title.required' => 'Judul pekerjaan wajib diisi',
            'location.required' => 'Lokasi pengerjaan wajib diisi',
            'priority.required' => 'Prioritas pekerjaan wajib dipilih',
        ];
    }
}
