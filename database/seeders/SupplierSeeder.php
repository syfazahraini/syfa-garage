<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /** Data dummy (nama, telepon, dan email fiktif). */
    public function run(): void
    {
        $suppliers = [
            ['name' => 'CV Sumber Pelumas Jaya', 'phone' => '021-5550101', 'email' => 'kontak@sumberpelumas.test'],
            ['name' => 'PT Ban Nusantara Motor', 'phone' => '021-5550102', 'email' => 'sales@bannusantara.test'],
            ['name' => 'UD Sparepart Mandiri', 'phone' => '021-5550103', 'email' => 'order@sparepartmandiri.test'],
            ['name' => 'PT Aki dan Kelistrikan Sentosa', 'phone' => '021-5550104', 'email' => 'info@akisentosa.test'],
        ];

        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
    }
}
