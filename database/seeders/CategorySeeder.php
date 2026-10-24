<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Oli & Pelumas',
            'Ban & Velg',
            'Kampas & Rem',
            'Busi & Kelistrikan',
            'Aki',
            'Filter',
            'Rantai & Gir',
            'Aksesoris',
        ];

        foreach ($names as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
