<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesInventoryData;
use Tests\TestCase;

/** Skenario 2: CRUD produk dan validasinya (SKU unik, harga tidak negatif). */
class ProductTest extends TestCase
{
    use MakesInventoryData;
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function payload(array $override = []): array
    {
        return $override + [
            'sku' => 'busi-001',
            'name' => 'Busi Standar',
            'category_id' => $this->category()->id,
            'purchase_price' => 12000,
            'selling_price' => 18000,
            'minimum_stock' => 5,
        ];
    }

    public function test_admin_can_create_product_with_zero_initial_stock(): void
    {
        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->payload(['stock' => 999])) // stok dari input harus diabaikan
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', ['sku' => 'BUSI-001', 'stock' => 0]); // SKU dirapikan jadi huruf besar
    }

    public function test_duplicate_sku_is_rejected(): void
    {
        $this->product(['sku' => 'BUSI-001']);

        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->payload(['sku' => 'busi-001']))
            ->assertSessionHasErrors('sku');

        $this->assertSame(1, Product::count());
    }

    public function test_negative_prices_are_rejected(): void
    {
        $this->actingAs($this->admin())
            ->post(route('products.store'), $this->payload(['purchase_price' => -1, 'selling_price' => -500]))
            ->assertSessionHasErrors(['purchase_price', 'selling_price']);

        $this->assertSame(0, Product::count());
    }

    public function test_required_fields_are_validated(): void
    {
        $this->actingAs($this->admin())
            ->post(route('products.store'), [])
            ->assertSessionHasErrors(['sku', 'name', 'category_id', 'purchase_price', 'selling_price', 'minimum_stock']);
    }

    public function test_admin_can_update_product_but_stock_is_not_changed(): void
    {
        $product = $this->product(['stock' => 7]);

        $this->actingAs($this->admin())
            ->put(route('products.update', $product), $this->payload([
                'sku' => $product->sku,
                'name' => 'Nama Baru',
                'stock' => 500,
            ]))
            ->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertSame('Nama Baru', $product->name);
        $this->assertSame(7, $product->stock);
    }

    public function test_deleting_product_is_a_soft_delete(): void
    {
        $product = $this->product();

        $this->actingAs($this->admin())->delete(route('products.destroy', $product))->assertRedirect();

        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
