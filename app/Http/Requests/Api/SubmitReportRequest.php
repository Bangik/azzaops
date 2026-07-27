<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SubmitReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'findings' => ['required', 'string'],
            'work_done' => ['required', 'string'],
            'recommendations' => ['nullable', 'string'],
            'materials_used' => ['nullable', 'string'],
            'photos' => ['nullable', 'array'],
            'photos.*.file' => ['required', 'file', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // max 5MB
            'photos.*.type' => ['required', 'string', 'in:before,progress,after'],
            'photos.*.caption' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'findings.required' => 'Temuan wajib diisi',
            'work_done.required' => 'Pekerjaan yang dilakukan wajib diisi',
            'photos.*.file.image' => 'File harus berupa foto/gambar',
            'photos.*.file.mimes' => 'Format foto tidak didukung (gunakan jpeg, png, jpg)',
            'photos.*.file.max' => 'Ukuran foto maksimal adalah 5MB',
            'photos.*.type.in' => 'Tipe foto tidak valid',
        ];
    }
}
