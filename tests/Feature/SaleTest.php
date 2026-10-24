<?php

namespace Tests\Feature;

use App\Exceptions\BusinessRuleException;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\SaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\Concerns\MakesInventoryData;
use Tests\TestCase;

/** Skenario 4-6: penjualan, stok tidak cukup, dan atomicity (semua berhasil atau semua batal). */
class SaleTest extends TestCase
{
    use MakesInventoryData;
    use RefreshDatabase;

    public function test_staff_sale_reduces_stock_and_calculates_total_from_database_price(): void
    {
        $oil = $this->product(['stock' => 10, 'selling_price' => 50000]);
        $plug = $this->product(['stock' => 20, 'selling_price' => 12000]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), [
                'items' => [
                    // unit_price dari browser harus diabaikan; harga selalu dari database
                    ['product_id' => $oil->id, 'quantity' => 2, 'unit_price' => 1],
                    ['product_id' => $plug->id, 'quantity' => 3, 'unit_price' => 1],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(8, $oil->fresh()->stock);
        $this->assertSame(17, $plug->fresh()->stock);

        $sale = Sale::firstOrFail();
        $this->assertEquals(136000.0, (float) $sale->total); // 2 x 50.000 + 3 x 12.000
        $this->assertSame(2, $sale->items()->count());
        $this->assertDatabaseHas('sale_items', ['product_id' => $oil->id, 'quantity' => 2, 'unit_price' => 50000]);
    }

    public function test_sale_is_rejected_when_stock_is_not_enough(): void
    {
        $product = $this->product(['stock' => 3]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 4]]])
            ->assertSessionHas('error');

        $this->assertSame(3, $product->fresh()->stock);
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
    }

    public function test_selling_exactly_all_remaining_stock_is_allowed(): void
    {
        $product = $this->product(['stock' => 3]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 3]]])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $product->fresh()->stock);
    }

    public function test_one_insufficient_item_rejects_the_whole_sale(): void
    {
        $enough = $this->product(['stock' => 10]);
        $short = $this->product(['stock' => 1]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), [
                'items' => [
                    ['product_id' => $enough->id, 'quantity' => 5],
                    ['product_id' => $short->id, 'quantity' => 2],
                ],
            ])
            ->assertSessionHas('error');

        // Produk yang stoknya cukup pun tidak boleh ikut berkurang
        $this->assertSame(10, $enough->fresh()->stock);
        $this->assertSame(1, $short->fresh()->stock);
        $this->assertSame(0, Sale::count());
    }

    public function test_failure_in_the_middle_of_a_sale_rolls_everything_back(): void
    {
        $first = $this->product(['stock' => 10]);
        $second = $this->product(['stock' => 10]);
        $staff = $this->staff();

        // Simulasi gangguan: item pertama sudah tersimpan dan stoknya berkurang, item kedua gagal disimpan
        $calls = 0;
        SaleItem::creating(function () use (&$calls) {
            $calls++;
            if ($calls === 2) {
                throw new RuntimeException('Simulasi gangguan database');
            }
        });

        try {
            app(SaleService::class)->create($staff, [
                'items' => [
                    ['product_id' => $first->id, 'quantity' => 4],
                    ['product_id' => $second->id, 'quantity' => 4],
                ],
            ]);
            $this->fail('Seharusnya melempar exception.');
        } catch (RuntimeException $e) {
            $this->assertSame('Simulasi gangguan database', $e->getMessage());
        } finally {
            SaleItem::flushEventListeners();
        }

        // Atomicity: tidak ada transaksi, tidak ada item, dan kedua stok kembali seperti semula
        $this->assertSame(0, Sale::count());
        $this->assertSame(0, SaleItem::count());
        $this->assertSame(10, $first->fresh()->stock);
        $this->assertSame(10, $second->fresh()->stock);
    }

    public function test_sale_requires_at_least_one_valid_item(): void
    {
        $this->actingAs($this->staff())
            ->post(route('sales.store'), ['items' => []])
            ->assertSessionHasErrors('items');

        $product = $this->product();

        $this->actingAs($this->staff())
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 0]]])
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertSame(0, Sale::count());
    }

    public function test_deleted_product_cannot_be_sold(): void
    {
        $product = $this->product(['stock' => 5]);
        $product->delete();

        $this->actingAs($this->staff())
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]])
            ->assertSessionHasErrors('items.0.product_id');

        $this->assertSame(0, Sale::count());
    }

    public function test_admin_cancelling_a_sale_restores_stock(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 4]]]);
        $this->assertSame(6, $product->fresh()->stock);

        $sale = Sale::firstOrFail();

        $this->actingAs($this->admin())->post(route('sales.cancel', $sale))->assertRedirect();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(Sale::STATUS_CANCELLED, $sale->fresh()->status);
    }

    public function test_a_sale_cannot_be_cancelled_twice(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->staff())
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 4]]]);

        $sale = Sale::firstOrFail();
        $admin = $this->admin();

        $this->actingAs($admin)->post(route('sales.cancel', $sale));
        $this->actingAs($admin)->post(route('sales.cancel', $sale))->assertSessionHas('error');

        // Stok tidak boleh bertambah dua kali
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_service_throws_business_rule_exception_for_insufficient_stock(): void
    {
        $product = $this->product(['stock' => 1]);

        $this->expectException(BusinessRuleException::class);

        app(SaleService::class)->create($this->staff(), [
            'items' => [['product_id' => $product->id, 'quantity' => 2]],
        ]);
    }
}
