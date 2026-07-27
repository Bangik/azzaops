<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'company_name', 'value' => 'PT. Azza Karunia Jaya', 'group' => 'company', 'description' => 'Nama perusahaan'],
            ['key' => 'company_address', 'value' => '', 'group' => 'company', 'description' => 'Alamat perusahaan'],
            ['key' => 'company_phone', 'value' => '', 'group' => 'company', 'description' => 'Nomor telepon perusahaan'],
            ['key' => 'company_wa', 'value' => '', 'group' => 'company', 'description' => 'Nomor WhatsApp perusahaan'],
            ['key' => 'company_email', 'value' => '', 'group' => 'company', 'description' => 'Email perusahaan'],
            ['key' => 'company_logo', 'value' => '', 'group' => 'company', 'description' => 'Logo perusahaan'],
            ['key' => 'invoice_prefix', 'value' => 'INV', 'group' => 'invoice', 'description' => 'Prefix nomor invoice'],
            ['key' => 'wo_prefix', 'value' => 'WO', 'group' => 'invoice', 'description' => 'Prefix nomor work order'],
            ['key' => 'rab_prefix', 'value' => 'RAB', 'group' => 'invoice', 'description' => 'Prefix nomor RAB'],
            ['key' => 'invoice_footer', 'value' => '', 'group' => 'invoice', 'description' => 'Footer invoice'],
            ['key' => 'tax_default', 'value' => '0', 'group' => 'invoice', 'description' => 'Persentase pajak default'],
            ['key' => 'max_photo_size', 'value' => '5242880', 'group' => 'upload', 'description' => 'Ukuran maksimal foto (bytes)'],
            ['key' => 'max_photos_per_report', 'value' => '10', 'group' => 'upload', 'description' => 'Jumlah maksimal foto per laporan'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'group' => $setting['group'],
                    'description' => $setting['description'],
                ]
            );
        }
    }
}
