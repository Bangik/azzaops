<?php

namespace Database\Seeders;

use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'AC Residential',
            'AC Komersial',
            'Elektronik',
            'Instalasi Ducting',
            'Cuci AC',
            'Perawatan AC',
        ];

        foreach ($categories as $name) {
            ServiceCategory::updateOrCreate(
                ['name' => $name],
                ['name' => $name]
            );
        }
    }
}
