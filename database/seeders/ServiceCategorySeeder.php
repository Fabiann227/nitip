<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

/**
 * Fee scheme from the Nitip PDF (Fitur 2: Skema Biaya Transparan / Flat Fee).
 */
class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'code' => 'food_in_campus',
                'name' => 'Makanan Kantin Dalam',
                'description' => 'Makanan & minuman dari kantin gedung lain di dalam kampus. Jarak dekat, ditempuh jalan kaki.',
                'fee_min' => 3000,
                'fee_default' => 3500,
                'fee_max' => 4000,
                'requires_document' => false,
                'has_item_cost' => true,
                'icon' => 'restaurant',
                'sort_order' => 1,
            ],
            [
                'code' => 'food_off_campus',
                'name' => 'Makanan Luar Kampus',
                'description' => 'Makanan cepat saji / kopi di sekitar gerbang kampus. Kompensasi waktu keluar gerbang dan kendaraan.',
                'fee_min' => 5000,
                'fee_default' => 6000,
                'fee_max' => 7000,
                'requires_document' => false,
                'has_item_cost' => true,
                'icon' => 'fastfood',
                'sort_order' => 2,
            ],
            [
                'code' => 'print_copy',
                'name' => 'Print & Fotokopi Tugas',
                'description' => 'Print laporan, makalah, dan penjilidan. Flat jasa + biaya riil kertas sesuai struk.',
                'fee_min' => 3000,
                'fee_default' => 4000,
                'fee_max' => 5000,
                'requires_document' => true,
                'has_item_cost' => true,
                'icon' => 'print',
                'sort_order' => 3,
            ],
        ];

        foreach ($categories as $category) {
            ServiceCategory::query()->updateOrCreate(['code' => $category['code']], $category + ['is_active' => true]);
        }
    }
}
