<?php

namespace Tests\Feature;

use App\Models\Purchase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesInventoryData;
use Tests\TestCase;

/** Skenario 3: pembelian (stock-in) menambah stok dan bisa dibatalkan sesuai aturan. */
class PurchaseTest extends TestCase
{
    use MakesInventoryData;
    use RefreshDatabase;

    public function test_staff_purchase_increases_stock(): void
    {
        $product = $this->product(['stock' => 5]);
        $supplier = $this->supplier();

        $this->actingAs($this->staff())
            ->post(route('purchases.store'), [
                'supplier_id' => $supplier->id,
                'items' => [['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 9000]],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(15, $product->fresh()->stock);
        $this->assertDatabaseHas('purchases', ['supplier_id' => $supplier->id, 'status' => Purchase::STATUS_COMPLETED]);
        $this->assertDatabaseHas('purchase_items', ['product_id' => $product->id, 'quantity' => 10]);
    }

    public function test_purchase_requires_supplier_and_at_least_one_item(): void
    {
        $this->actingAs($this->staff())
            ->post(route('purchases.store'), ['items' => []])
            ->assertSessionHasErrors(['supplier_id', 'items']);

        $this->assertSame(0, Purchase::count());
    }

    public function test_purchase_quantity_must_be_positive(): void
    {
        $product = $this->product(['stock' => 5]);

        $this->actingAs($this->staff())
            ->post(route('purchases.store'), [
                'supplier_id' => $this->supplier()->id,
                'items' => [['product_id' => $product->id, 'quantity' => 0, 'unit_cost' => 9000]],
            ])
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(5, $product->fresh()->stock);
    }

    public function test_admin_can_cancel_purchase_and_stock_is_reverted(): void
    {
        $product = $this->product(['stock' => 5]);
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('purchases.store'), [
            'supplier_id' => $this->supplier()->id,
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 9000]],
        ]);
        $this->assertSame(15, $product->fresh()->stock);

        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->admin())->post(route('purchases.cancel', $purchase))->assertRedirect();

        $this->assertSame(5, $product->fresh()->stock);
        $this->assertSame(Purchase::STATUS_CANCELLED, $purchase->fresh()->status);
    }

    public function test_purchase_cannot_be_cancelled_if_goods_are_already_sold(): void
    {
        $product = $this->product(['stock' => 0]);
        $staff = $this->staff();

        $this->actingAs($staff)->post(route('purchases.store'), [
            'supplier_id' => $this->supplier()->id,
            'items' => [['product_id' => $product->id, 'quantity' => 10, 'unit_cost' => 9000]],
        ]);
        $this->actingAs($staff)->post(route('sales.store'), [
            'items' => [['product_id' => $product->id, 'quantity' => 8]],
        ]);
        $this->assertSame(2, $product->fresh()->stock);

        $purchase = Purchase::firstOrFail();

        $this->actingAs($this->admin())
            ->post(route('purchases.cancel', $purchase))
            ->assertSessionHas('error');

        // Ditolak: stok tidak boleh jadi negatif dan status tetap completed
        $this->assertSame(2, $product->fresh()->stock);
        $this->assertSame(Purchase::STATUS_COMPLETED, $purchase->fresh()->status);
    }
}
