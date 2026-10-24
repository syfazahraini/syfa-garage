<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Validasi untuk tambah dan ubah pemasok. */
class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-master') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pemasok wajib diisi.',
            'name.max' => 'Nama pemasok maksimal 150 karakter.',
            'phone.max' => 'Nomor telepon maksimal 30 karakter.',
            'phone.regex' => 'Nomor telepon hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'email.email' => 'Format email tidak valid.',
        ];
    }
}
