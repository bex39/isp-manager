<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name' => '10 Mbps Unlimited',
                'description' => 'Paket ekonomis untuk browsing dan streaming',
                'download_speed' => 10,
                'upload_speed' => 5,
                'price' => 150000,
                'has_fup' => false,
                'billing_cycle' => 'monthly',
                'available_for' => ['pppoe', 'static', 'dhcp'],
                'is_active' => true,
            ],
            [
                'name' => '20 Mbps Unlimited',
                'description' => 'Paket standar untuk keluarga',
                'download_speed' => 20,
                'upload_speed' => 10,
                'price' => 250000,
                'has_fup' => false,
                'billing_cycle' => 'monthly',
                'available_for' => ['pppoe', 'static', 'dhcp'],
                'is_active' => true,
            ],
            [
                'name' => '50 Mbps Unlimited',
                'description' => 'Paket premium untuk gaming dan 4K streaming',
                'download_speed' => 50,
                'upload_speed' => 25,
                'price' => 450000,
                'has_fup' => false,
                'billing_cycle' => 'monthly',
                'available_for' => ['pppoe', 'static', 'dhcp'],
                'is_active' => true,
            ],
            [
                'name' => 'Hotspot 100GB',
                'description' => 'Paket hotspot bulanan dengan kuota',
                'download_speed' => 10,
                'upload_speed' => 5,
                'price' => 100000,
                'has_fup' => true,
                'fup_quota' => 100,
                'fup_speed' => 2,
                'billing_cycle' => 'monthly',
                'available_for' => ['hotspot'],
                'is_active' => true,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }
    }
}
