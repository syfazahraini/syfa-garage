<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Data dummy produk grosir sparepart motor.
     * Beberapa produk sengaja stoknya <= minimum_stock agar daftar "stok rendah" ada isinya.
     * Format: [kategori, sku, nama, harga beli, harga jual, stok, stok minimum]
     */
    public function run(): void
    {
        $products = [
            ['Oli & Pelumas', 'OLI-001', 'Oli Mesin Matic 0.8L 10W-30', 38000, 45000, 120, 30],
            ['Oli & Pelumas', 'OLI-002', 'Oli Mesin Motor Bebek 1L 20W-50', 42000, 50000, 90, 25],
            ['Oli & Pelumas', 'OLI-003', 'Oli Gardan Matic 120ml', 9000, 12000, 150, 40],
            ['Oli & Pelumas', 'OLI-004', 'Minyak Rem DOT 3 100ml', 14000, 18000, 8, 15],

            ['Ban & Velg', 'BAN-001', 'Ban Luar Tubeless 80/90-14', 185000, 220000, 24, 10],
            ['Ban & Velg', 'BAN-002', 'Ban Luar 70/90-17', 150000, 180000, 18, 10],
            ['Ban & Velg', 'BAN-003', 'Ban Dalam 250/275-17', 28000, 38000, 60, 20],

            ['Kampas & Rem', 'REM-001', 'Kampas Rem Depan Matic', 22000, 32000, 70, 20],
            ['Kampas & Rem', 'REM-002', 'Kampas Rem Belakang Motor Bebek', 18000, 27000, 55, 20],
            ['Kampas & Rem', 'REM-003', 'Piringan Cakram Depan', 85000, 115000, 6, 10],

            ['Busi & Kelistrikan', 'BSI-001', 'Busi Standar CPR9EA', 14000, 20000, 200, 50],
            ['Busi & Kelistrikan', 'BSI-002', 'Busi Iridium', 78000, 105000, 30, 10],
            ['Busi & Kelistrikan', 'LST-001', 'Bohlam Headlamp H4 35/35W', 18000, 27000, 7, 15],

            ['Aki', 'AKI-001', 'Aki Kering 12V 5Ah', 135000, 170000, 20, 8],
            ['Aki', 'AKI-002', 'Aki Kering 12V 3.5Ah', 105000, 135000, 14, 8],

            ['Filter', 'FLT-001', 'Filter Udara Matic', 25000, 37000, 45, 15],
            ['Filter', 'FLT-002', 'Filter Oli Universal', 12000, 18000, 80, 20],

            ['Rantai & Gir', 'RGR-001', 'Rantai Motor 428H 120L', 55000, 78000, 25, 10],
            ['Rantai & Gir', 'RGR-002', 'Gir Set Motor Bebek', 95000, 130000, 5, 8],

            ['Aksesoris', 'AKS-001', 'Spion Standar Universal (sepasang)', 35000, 52000, 40, 12],
            ['Aksesoris', 'AKS-002', 'Grip Handle Karet', 20000, 30000, 50, 15],
            ['Aksesoris', 'AKS-003', 'Cover Motor Waterproof', 65000, 90000, 30, 10],
        ];

        foreach ($products as [$categoryName, $sku, $name, $buy, $sell, $stock, $min]) {
            $category = Category::where('name', $categoryName)->firstOrFail();

            Product::updateOrCreate(
                ['sku' => $sku],
                [
                    'category_id' => $category->id,
                    'name' => $name,
                    'purchase_price' => $buy,
                    'selling_price' => $sell,
                    'stock' => $stock,
                    'minimum_stock' => $min,
                ]
            );
        }
    }
}
