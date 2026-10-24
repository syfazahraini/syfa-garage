<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi penjualan. Perhatikan: TIDAK ADA harga di input.
 * Harga selalu diambil server dari database.
 */
class StoreSaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-sales') ?? false;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct', // produk yang sama tidak boleh muncul dua kali
                Rule::exists('products', 'id')->whereNull('deleted_at'), // produk yang sudah dihapus tidak bisa dijual
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Tambahkan minimal satu produk.',
            'items.array' => 'Data item tidak valid.',
            'items.min' => 'Tambahkan minimal satu produk.',
            'items.max' => 'Maksimal 50 item dalam satu transaksi.',

            'items.*.product_id.required' => 'Pilih produk pada setiap baris.',
            'items.*.product_id.distinct' => 'Produk yang sama tidak boleh dipilih dua kali.',
            'items.*.product_id.exists' => 'Produk yang dipilih tidak tersedia.',

            'items.*.quantity.required' => 'Jumlah wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah harus berupa bilangan bulat.',
            'items.*.quantity.min' => 'Jumlah minimal 1.',
            'items.*.quantity.max' => 'Jumlah terlalu besar.',
        ];
    }
}
