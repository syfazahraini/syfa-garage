<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesInventoryData;
use Tests\TestCase;

/**
 * Skenario 7: authorization di server (bukan sekadar menyembunyikan tombol).
 * Admin = kelola master, user, laporan, pembatalan.
 * Staff = membuat pembelian dan penjualan, hanya melihat transaksi miliknya.
 * User  = pelanggan: hanya melihat produk dan dashboard.
 */
class AuthorizationTest extends TestCase
{
    use MakesInventoryData;
    use RefreshDatabase;

    private function saleBy(User $user, string $number = 'INV-TEST-1', string $status = Sale::STATUS_COMPLETED): Sale
    {
        return Sale::create([
            'user_id' => $user->id,
            'sale_number' => $number,
            'total' => 15000,
            'status' => $status,
            'sold_at' => now(),
        ]);
    }

    // ---------- Staff ----------

    public function test_staff_cannot_open_admin_only_pages(): void
    {
        $staff = $this->staff();

        foreach (['users.index', 'users.create', 'categories.index', 'suppliers.index', 'products.create'] as $route) {
            $this->actingAs($staff)->get(route($route))->assertForbidden();
        }
    }

    public function test_staff_cannot_create_or_delete_products_via_direct_request(): void
    {
        $staff = $this->staff();
        $product = $this->product();

        $this->actingAs($staff)->post(route('products.store'), [
            'sku' => 'HACK-1', 'name' => 'Hack', 'category_id' => $product->category_id,
            'purchase_price' => 1, 'selling_price' => 1, 'minimum_stock' => 1,
        ])->assertForbidden();

        $this->actingAs($staff)->delete(route('products.destroy', $product))->assertForbidden();

        $this->assertSame(1, Product::count());
        $this->assertNull($product->fresh()->deleted_at);
    }

    public function test_staff_can_view_products_and_create_transactions_pages(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get(route('products.index'))->assertOk();
        $this->actingAs($staff)->get(route('sales.create'))->assertOk();
        $this->actingAs($staff)->get(route('purchases.create'))->assertOk();
    }

    public function test_staff_cannot_cancel_transactions(): void
    {
        $staff = $this->staff();
        $sale = $this->saleBy($staff);

        $this->actingAs($staff)->post(route('sales.cancel', $sale))->assertForbidden();

        $this->assertSame(Sale::STATUS_COMPLETED, $sale->fresh()->status);
    }

    public function test_staff_only_sees_their_own_sales(): void
    {
        $staffA = $this->staff();
        $staffB = $this->staff();
        $mine = $this->saleBy($staffA, 'INV-MINE');
        $theirs = $this->saleBy($staffB, 'INV-THEIRS');

        $this->actingAs($staffA)->get(route('sales.show', $mine))->assertOk();
        $this->actingAs($staffA)->get(route('sales.show', $theirs))->assertForbidden();

        $this->actingAs($staffA)->get(route('sales.index'))
            ->assertOk()
            ->assertSee('INV-MINE')
            ->assertDontSee('INV-THEIRS');
    }

    // ---------- Admin ----------

    public function test_admin_can_see_all_sales(): void
    {
        $staff = $this->staff();
        $sale = $this->saleBy($staff, 'INV-STAFF');
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('sales.show', $sale))->assertOk();
        $this->actingAs($admin)->get(route('sales.index'))->assertOk()->assertSee('INV-STAFF');
    }

    public function test_admin_does_not_create_transactions(): void
    {
        // Pembelian dan penjualan adalah tugas Staff (sesuai panduan UTS)
        $admin = $this->admin();
        $product = $this->product(['stock' => 10]);

        $this->actingAs($admin)->get(route('sales.create'))->assertForbidden();
        $this->actingAs($admin)->get(route('purchases.create'))->assertForbidden();

        $this->actingAs($admin)
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]])
            ->assertForbidden();

        $this->assertSame(0, Sale::count());
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_admin_can_create_a_user_account_for_a_customer(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name' => 'Pelanggan Baru',
                'email' => 'pelanggan@example.test',
                'password' => 'rahasia123',
                'password_confirmation' => 'rahasia123',
                'role' => User::ROLE_USER,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'pelanggan@example.test', 'role' => User::ROLE_USER]);
    }

    public function test_invalid_role_is_rejected_when_creating_a_user(): void
    {
        $this->actingAs($this->admin())
            ->post(route('users.store'), [
                'name' => 'Aneh', 'email' => 'aneh@example.test',
                'password' => 'rahasia123', 'password_confirmation' => 'rahasia123',
                'role' => 'superadmin',
            ])
            ->assertSessionHasErrors('role');
    }

    // ---------- User (pelanggan) ----------

    public function test_customer_can_view_dashboard_and_products(): void
    {
        $customer = $this->customer();
        $product = $this->product();

        $this->actingAs($customer)->get(route('dashboard'))->assertOk();
        $this->actingAs($customer)->get(route('products.index'))->assertOk();
        $this->actingAs($customer)->get(route('products.show', $product))->assertOk();
    }

    public function test_customer_cannot_open_transactions_or_management_pages(): void
    {
        $customer = $this->customer();

        $routes = [
            'sales.index', 'sales.create', 'purchases.index', 'purchases.create',
            'users.index', 'categories.index', 'suppliers.index',
            'products.create', 'products.low-stock',
        ];

        foreach ($routes as $route) {
            $this->actingAs($customer)->get(route($route))->assertForbidden();
        }
    }

    public function test_customer_cannot_create_a_sale_by_posting_directly(): void
    {
        $product = $this->product(['stock' => 10]);

        $this->actingAs($this->customer())
            ->post(route('sales.store'), ['items' => [['product_id' => $product->id, 'quantity' => 1]]])
            ->assertForbidden();

        $this->assertSame(0, Sale::count());
        $this->assertSame(10, $product->fresh()->stock);
    }

    public function test_customer_does_not_see_purchase_price_but_staff_does(): void
    {
        $product = $this->product(['purchase_price' => 123456, 'selling_price' => 200000]);

        $this->actingAs($this->customer())
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertDontSee('Harga beli')
            ->assertDontSee('123.456')
            ->assertSee('200.000');

        $this->actingAs($this->staff())
            ->get(route('products.show', $product))
            ->assertSee('Harga beli')
            ->assertSee('123.456');
    }
}
