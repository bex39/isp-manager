<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@ispmanager.test',
            'password' => Hash::make('password'),
            'phone' => '081234567890',
            'status' => 'active',
        ]);
        $superAdmin->assignRole('super_admin');

        // Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin.user@ispmanager.test',
            'password' => Hash::make('password'),
            'phone' => '081234567891',
            'status' => 'active',
        ]);
        $admin->assignRole('admin');

        // CS
        $cs = User::create([
            'name' => 'CS User',
            'email' => 'cs@ispmanager.test',
            'password' => Hash::make('password'),
            'phone' => '081234567892',
            'status' => 'active',
        ]);
        $cs->assignRole('cs');

        // Teknisi
        $teknisi = User::create([
            'name' => 'Teknisi User',
            'email' => 'teknisi@ispmanager.test',
            'password' => Hash::make('password'),
            'phone' => '081234567893',
            'status' => 'active',
        ]);
        $teknisi->assignRole('teknisi');
    }
}
