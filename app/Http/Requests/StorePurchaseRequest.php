<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-purchases') ?? false;
    }

    public function rules(): array
    {
        return [
            'supplier_id' => ['required', 'integer', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct', // produk yang sama tidak boleh muncul dua kali
                Rule::exists('products', 'id')->whereNull('deleted_at'), // produk yang sudah dihapus tidak bisa dibeli
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0', 'max:9999999999'],
        ];
    }

    public function messages(): array
    {
        return [
            'supplier_id.required' => 'Pemasok wajib dipilih.',
            'supplier_id.exists' => 'Pemasok yang dipilih tidak tersedia.',

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

            'items.*.unit_cost.required' => 'Harga beli wajib diisi.',
            'items.*.unit_cost.numeric' => 'Harga beli harus berupa angka.',
            'items.*.unit_cost.min' => 'Harga beli tidak boleh negatif.',
            'items.*.unit_cost.max' => 'Harga beli terlalu besar.',
        ];
    }
}
