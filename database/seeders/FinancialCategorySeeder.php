<?php

namespace Database\Seeders;

use App\Models\FinancialCategory;
use Illuminate\Database\Seeder;

class FinancialCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Pembayaran Jasa', 'type' => 'income'],
            ['name' => 'Pembayaran Material', 'type' => 'income'],
            ['name' => 'Pembelian Material', 'type' => 'expense'],
            ['name' => 'Transport', 'type' => 'expense'],
            ['name' => 'Gaji', 'type' => 'expense'],
            ['name' => 'Operasional Kantor', 'type' => 'expense'],
            ['name' => 'Lain-lain', 'type' => 'expense'],
        ];

        foreach ($categories as $cat) {
            FinancialCategory::updateOrCreate(
                ['name' => $cat['name']],
                ['type' => $cat['type']]
            );
        }
    }
}
