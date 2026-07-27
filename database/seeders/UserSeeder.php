<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            ['name' => 'Super Admin', 'email' => 'superadmin@azzaops.test', 'role' => 'super_admin'],
            ['name' => 'Admin CS', 'email' => 'admin@azzaops.test', 'role' => 'admin'],
            ['name' => 'Ahmad Kepala', 'email' => 'kepala@azzaops.test', 'role' => 'kepala_teknisi'],
            ['name' => 'Budi Teknisi', 'email' => 'teknisi1@azzaops.test', 'role' => 'teknisi'],
            ['name' => 'Dedi Teknisi', 'email' => 'teknisi2@azzaops.test', 'role' => 'teknisi'],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role' => $user['role'],
                ]
            );
        }
    }
}
