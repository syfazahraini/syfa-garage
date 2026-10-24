<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validasi untuk tambah dan ubah produk.
 * Stok TIDAK ada di sini: stok hanya berubah lewat transaksi pembelian/penjualan.
 */
class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-master') ?? false;
    }

    /** Rapikan SKU (hapus spasi, huruf besar) sebelum divalidasi. */
    protected function prepareForValidation(): void
    {
        if (is_string($this->input('sku'))) {
            $this->merge(['sku' => strtoupper(trim($this->input('sku')))]);
        }
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                // Saat ubah data, abaikan produk yang sedang diedit
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'purchase_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'selling_price' => ['required', 'numeric', 'min:0', 'max:9999999999'],
            'minimum_stock' => ['required', 'integer', 'min:0', 'max:1000000'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.required' => 'SKU wajib diisi.',
            'sku.max' => 'SKU maksimal 50 karakter.',
            'sku.unique' => 'SKU ini sudah dipakai produk lain.',
            'name.required' => 'Nama produk wajib diisi.',
            'name.max' => 'Nama produk maksimal 150 karakter.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori yang dipilih tidak tersedia.',
            'purchase_price.required' => 'Harga beli wajib diisi.',
            'purchase_price.numeric' => 'Harga beli harus berupa angka.',
            'purchase_price.min' => 'Harga beli tidak boleh negatif.',
            'purchase_price.max' => 'Harga beli terlalu besar.',
            'selling_price.required' => 'Harga jual wajib diisi.',
            'selling_price.numeric' => 'Harga jual harus berupa angka.',
            'selling_price.min' => 'Harga jual tidak boleh negatif.',
            'selling_price.max' => 'Harga jual terlalu besar.',
            'minimum_stock.required' => 'Stok minimum wajib diisi.',
            'minimum_stock.integer' => 'Stok minimum harus berupa bilangan bulat.',
            'minimum_stock.min' => 'Stok minimum tidak boleh negatif.',
            'minimum_stock.max' => 'Stok minimum terlalu besar.',
        ];
    }
}
