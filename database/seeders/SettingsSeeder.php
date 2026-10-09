<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            ['key' => 'restaurant_name',      'value' => 'ZAC Burger Club', 'label' => 'Restaurant Name'],
            ['key' => 'currency',             'value' => 'USD',              'label' => 'Currency'],
            ['key' => 'currency_symbol',      'value' => '$',                'label' => 'Currency Symbol'],
            ['key' => 'default_delivery_charge', 'value' => '5.00',         'label' => 'Default Delivery Charge'],
        ];

        foreach ($defaults as $row) {
            Setting::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
