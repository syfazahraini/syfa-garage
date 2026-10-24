<?php

namespace Tests\Concerns;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;

/**
 * Helper pembuat data uji (user per role, kategori, pemasok, produk).
 * Dipakai bersama oleh semua Feature test.
 */
trait MakesInventoryData
{
    private int $productCounter = 0;

    protected function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    protected function staff(): User
    {
        return User::factory()->create(['role' => User::ROLE_STAFF]);
    }

    protected function customer(): User
    {
        return User::factory()->create(['role' => User::ROLE_USER]);
    }

    protected function category(string $name = 'Oli Pelumas'): Category
    {
        return Category::firstOrCreate(['name' => $name]);
    }

    protected function supplier(string $name = 'CV Maju Jaya'): Supplier
    {
        return Supplier::create(['name' => $name]);
    }

    /** @param array<string, mixed> $attributes */
    protected function product(array $attributes = []): Product
    {
        $this->productCounter++;

        return Product::create($attributes + [
            'category_id' => $this->category()->id,
            'sku' => 'SKU-' . str_pad((string) $this->productCounter, 3, '0', STR_PAD_LEFT),
            'name' => 'Produk Uji ' . $this->productCounter,
            'purchase_price' => 10000,
            'selling_price' => 15000,
            'stock' => 10,
            'minimum_stock' => 5,
        ]);
    }
}
