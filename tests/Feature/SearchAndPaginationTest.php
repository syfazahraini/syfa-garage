<?php

namespace Tests\Feature;

use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MakesInventoryData;
use Tests\TestCase;

/** Skenario 8: pencarian, filter, dan pagination. */
class SearchAndPaginationTest extends TestCase
{
    use MakesInventoryData;
    use RefreshDatabase;

    public function test_products_can_be_searched_by_name_and_sku(): void
    {
        $this->product(['sku' => 'BUSI-NGK', 'name' => 'Busi Iridium']);
        $this->product(['sku' => 'MESIN-001', 'name' => 'Mesin Oli Sintetik']);
        $staff = $this->staff();

        $this->actingAs($staff)->get(route('products.index', ['q' => 'iridium']))
            ->assertOk()
            ->assertSee('Busi Iridium')
            ->assertDontSee('Mesin Oli Sintetik');

        $this->actingAs($staff)->get(route('products.index', ['q' => 'MESIN-001']))
            ->assertSee('Mesin Oli Sintetik')
            ->assertDontSee('Busi Iridium');
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $tires = $this->category('Ban Velg');
        $oils = $this->category('Oli Pelumas');
        $this->product(['category_id' => $tires->id, 'name' => 'Ban Tubeless']);
        $this->product(['category_id' => $oils->id, 'name' => 'Pelumas Rantai']);

        $this->actingAs($this->staff())
            ->get(route('products.index', ['category_id' => $tires->id]))
            ->assertSee('Ban Tubeless')
            ->assertDontSee('Pelumas Rantai');
    }

    public function test_search_with_no_match_shows_empty_state_without_error(): void
    {
        $this->product(['name' => 'Busi Standar']);

        $this->actingAs($this->staff())
            ->get(route('products.index', ['q' => 'tidak-ada-produk-ini']))
            ->assertOk()
            ->assertSee('Produk tidak ditemukan');
    }

    public function test_search_treats_sql_wildcards_as_plain_text(): void
    {
        $this->product(['name' => 'Busi Standar']);

        // "%" tidak boleh dianggap wildcard yang cocok dengan semua produk
        $this->actingAs($this->staff())
            ->get(route('products.index', ['q' => '%']))
            ->assertOk()
            ->assertDontSee('Busi Standar');
    }

    public function test_product_list_is_paginated(): void
    {
        for ($i = 0; $i < 15; $i++) {
            $this->product();
        }
        $staff = $this->staff();

        $this->actingAs($staff)->get(route('products.index'))
            ->assertViewHas('products', fn ($page) => $page->count() === 10 && $page->total() === 15);

        $this->actingAs($staff)->get(route('products.index', ['page' => 2]))
            ->assertViewHas('products', fn ($page) => $page->count() === 5);
    }

    public function test_sales_can_be_filtered_by_status_and_date_and_number(): void
    {
        $staff = $this->staff();
        $admin = $this->admin();

        Sale::create(['user_id' => $staff->id, 'sale_number' => 'INV-OKT-A', 'total' => 1000,
            'status' => Sale::STATUS_COMPLETED, 'sold_at' => '2026-10-01 10:00:00']);
        Sale::create(['user_id' => $staff->id, 'sale_number' => 'INV-OKT-B', 'total' => 2000,
            'status' => Sale::STATUS_CANCELLED, 'sold_at' => '2026-10-20 10:00:00']);

        $this->actingAs($admin)->get(route('sales.index', ['status' => 'cancelled']))
            ->assertViewHas('sales', fn ($page) => $page->total() === 1)
            ->assertSee('INV-OKT-B')
            ->assertDontSee('INV-OKT-A');

        $this->actingAs($admin)->get(route('sales.index', ['date_from' => '2026-10-15']))
            ->assertViewHas('sales', fn ($page) => $page->total() === 1)
            ->assertSee('INV-OKT-B');

        $this->actingAs($admin)->get(route('sales.index', ['date_to' => '2026-10-05']))
            ->assertViewHas('sales', fn ($page) => $page->total() === 1)
            ->assertSee('INV-OKT-A');

        $this->actingAs($admin)->get(route('sales.index', ['q' => 'OKT-A']))
            ->assertViewHas('sales', fn ($page) => $page->total() === 1);
    }

    public function test_invalid_filter_values_are_ignored_not_crashing(): void
    {
        $this->actingAs($this->admin())
            ->get(route('sales.index', ['status' => 'ngawur', 'date_from' => 'bukan-tanggal']))
            ->assertOk();
    }
}
