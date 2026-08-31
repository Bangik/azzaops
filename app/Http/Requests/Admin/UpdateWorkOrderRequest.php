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
        $rules = [
            'work_order_type_id' => ['required', 'exists:work_order_types,id'],
            'service_category_id' => ['required', 'exists:service_categories,id'],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['required', 'string'],
            'gmaps_link' => ['nullable', 'string'],
            'scheduled_date' => ['nullable', 'date'],
            'scheduled_time' => ['nullable', 'string'],
            'job_order' => ['nullable', 'integer', 'min:0'],
            'items' => ['nullable', 'array'],
            'items.*.description' => ['required_with:items', 'string', 'max:255'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.unit_price' => ['required_with:items', 'numeric', 'min:0'],
            'items.*.vendor_unit_price' => ['nullable', 'numeric', 'min:0'],
        ];

        // admin cannot edit customer, so customer_id is not required from request if role is admin
        if ($this->user()->role->value === 'admin') {
            $rules['customer_id'] = ['nullable'];
        } else {
            $rules['customer_id'] = ['required', 'exists:customers,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'work_order_type_id.required' => 'Tipe pekerjaan wajib dipilih',
            'customer_id.required' => 'Customer wajib dipilih',
            'service_category_id.required' => 'Kategori layanan wajib dipilih',
            'title.required' => 'Judul pekerjaan wajib diisi',
            'location.required' => 'Lokasi pengerjaan wajib diisi',
        ];
    }
}
