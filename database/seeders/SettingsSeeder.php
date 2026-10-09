<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Services\ThemeService;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $general = [
            ['key' => 'restaurant_name',         'value' => 'ZAC Burger Club', 'label' => 'Restaurant Name'],
            ['key' => 'currency',                'value' => 'USD',              'label' => 'Currency'],
            ['key' => 'currency_symbol',         'value' => '$',                'label' => 'Currency Symbol'],
            ['key' => 'default_delivery_charge', 'value' => '5.00',            'label' => 'Default Delivery Charge'],
        ];

        $themeLabels = [
            'theme_primary_color'  => 'Primary Color',
            'theme_accent_color'   => 'Accent Color',
            'theme_default_mode'   => 'Default Mode',
            'theme_corner_style'   => 'Corner Style',
            'theme_sidebar_style'  => 'Sidebar Style',
            'theme_font_size'      => 'Font Size',
            'theme_density'        => 'Density',
        ];

        $theme = array_map(
            fn ($key, $value) => ['key' => $key, 'value' => $value, 'label' => $themeLabels[$key]],
            array_keys(ThemeService::DEFAULTS),
            ThemeService::DEFAULTS
        );

        foreach (array_merge($general, $theme) as $row) {
            Setting::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
