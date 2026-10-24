<?php

namespace Tests\Feature;

use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesInventoryData;
use Tests\TestCase;

/** Dashboard menyesuaikan isi dengan role dan hanya menghitung transaksi yang selesai. */
class DashboardTest extends TestCase
{
    use MakesInventoryData;
    use RefreshDatabase;

    public function test_admin_dashboard_counts_completed_sales_only(): void
    {
        $staff = $this->staff();

        Sale::create(['user_id' => $staff->id, 'sale_number' => 'INV-OK', 'total' => 100000,
            'status' => Sale::STATUS_COMPLETED, 'sold_at' => now()]);
        Sale::create(['user_id' => $staff->id, 'sale_number' => 'INV-BATAL', 'total' => 900000,
            'status' => Sale::STATUS_CANCELLED, 'sold_at' => now()]);

        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('salesToday', fn ($row) => (int) $row->trx === 1 && (float) $row->amount === 100000.0)
            ->assertSee('Penjualan hari ini')
            ->assertSee('Pembelian bulan ini');
    }

    public function test_admin_dashboard_lists_products_that_need_restocking(): void
    {
        $this->product(['name' => 'Kampas Rem Habis', 'stock' => 1, 'minimum_stock' => 5]);

        $this->actingAs($this->admin())
            ->get(route('dashboard'))
            ->assertSee('Perlu dibeli ulang')
            ->assertSee('Kampas Rem Habis');
    }

    public function test_staff_dashboard_only_counts_own_sales_and_hides_purchase_info(): void
    {
        $staffA = $this->staff();
        $staffB = $this->staff();

        Sale::create(['user_id' => $staffA->id, 'sale_number' => 'INV-A', 'total' => 100000,
            'status' => Sale::STATUS_COMPLETED, 'sold_at' => now()]);
        Sale::create(['user_id' => $staffB->id, 'sale_number' => 'INV-B', 'total' => 500000,
            'status' => Sale::STATUS_COMPLETED, 'sold_at' => now()]);

        $this->actingAs($staffA)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('salesMonth', fn ($row) => (int) $row->trx === 1 && (float) $row->amount === 100000.0)
            ->assertViewMissing('purchaseMonth')
            ->assertViewMissing('lowStockProducts')
            ->assertDontSee('Pembelian bulan ini')
            ->assertDontSee('Perlu dibeli ulang');
    }

    public function test_customer_dashboard_shows_catalog_but_no_transaction_data(): void
    {
        $this->product(['name' => 'Ban Depan Tersedia', 'stock' => 5]);
        $this->product(['name' => 'Ban Belakang Kosong', 'stock' => 0]);

        $this->actingAs($this->customer())
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('availableCount', 1)
            ->assertViewHas('outOfStockCount', 1)
            ->assertViewMissing('salesToday')
            ->assertViewMissing('lowStockCount')
            ->assertDontSee('Penjualan hari ini')
            ->assertDontSee('Pembelian bulan ini')
            ->assertDontSee('Perlu dibeli ulang');
    }
}
