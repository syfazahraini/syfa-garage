<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi untuk tambah dan ubah kategori. */
class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-master') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                // Saat ubah data, abaikan kategori yang sedang diedit
                Rule::unique('categories', 'name')->ignore($this->route('category')),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.max' => 'Nama kategori maksimal 100 karakter.',
            'name.unique' => 'Nama kategori ini sudah ada.',
        ];
    }
}
