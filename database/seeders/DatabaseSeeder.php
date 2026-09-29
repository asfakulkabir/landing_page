<?php

namespace Database\Seeders;

use App\Models\DeliveryZone;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrator',
                'password' => 'admin123',
                'email_verified_at' => now(),
            ]
        );

        $zones = [
            ['name' => 'Inside Dhaka City', 'charge' => 60, 'estimated_days' => 1],
            ['name' => 'Outside Dhaka City', 'charge' => 120, 'estimated_days' => 2],
            ['name' => 'Suburban Area', 'charge' => 100, 'estimated_days' => 3],
        ];

        foreach ($zones as $index => $zone) {
            DeliveryZone::updateOrCreate(
                ['name' => $zone['name']],
                array_merge($zone, ['sort_order' => $index + 1, 'is_active' => true])
            );
        }

        foreach (Setting::DEFAULTS as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $this->call(DemoContentSeeder::class);
    }
}
