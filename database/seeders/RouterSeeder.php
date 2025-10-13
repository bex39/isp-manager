<?php

namespace Database\Seeders;

use App\Models\Router;
use Illuminate\Database\Seeder;

class RouterSeeder extends Seeder
{
    public function run(): void
    {
        Router::create([
            'name' => 'Router Pusat 1',
            'ip_address' => '192.168.1.1',
            'username' => 'admin',
            'password' => 'admin123',
            'ros_version' => '7',
            'address' => 'Kantor Pusat',
            'latitude' => -8.670458,
            'longitude' => 115.212629,
            'coverage_radius' => 500,
            'is_active' => true,
        ]);

        Router::create([
            'name' => 'Router Area Denpasar',
            'ip_address' => '192.168.2.1',
            'username' => 'admin',
            'password' => 'admin123',
            'ros_version' => '7',
            'address' => 'Denpasar',
            'latitude' => -8.650000,
            'longitude' => 115.216667,
            'coverage_radius' => 1000,
            'is_active' => true,
        ]);
    }
}
