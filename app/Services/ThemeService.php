<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    const CACHE_KEY = 'theme_settings';

    const DEFAULTS = [
        'theme_primary_color'  => '#f97316',
        'theme_accent_color'   => '#fbbf24',
        'theme_default_mode'   => 'light',
        'theme_corner_style'   => 'rounded',
        'theme_sidebar_style'  => 'light',
        'theme_font_size'      => 'medium',
        'theme_density'        => 'comfortable',
    ];

    const PRESETS = [
        'burger-orange'  => ['primary' => '#f97316', 'accent' => '#fbbf24', 'label' => 'Burger Orange'],
        'ruby-red'       => ['primary' => '#dc2626', 'accent' => '#f59e0b', 'label' => 'Ruby Red'],
        'forest-green'   => ['primary' => '#16a34a', 'accent' => '#84cc16', 'label' => 'Forest Green'],
        'ocean-blue'     => ['primary' => '#2563eb', 'accent' => '#06b6d4', 'label' => 'Ocean Blue'],
        'royal-purple'   => ['primary' => '#7c3aed', 'accent' => '#ec4899', 'label' => 'Royal Purple'],
        'black-and-gold' => ['primary' => '#111827', 'accent' => '#d97706', 'label' => 'Black & Gold'],
    ];

    // ── Color utilities ────────────────────────────────────────────────────

    public static function isValidHex(string $hex): bool
    {
        return (bool) preg_match('/^#[0-9a-fA-F]{6}$/', $hex);
    }

    public static function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            (int) hexdec(substr($hex, 0, 2)),
            (int) hexdec(substr($hex, 2, 2)),
            (int) hexdec(substr($hex, 4, 2)),
        ];
    }

    /**
     * Generate the 50–900 shade scale from a single base hex color.
     * Returns an array keyed 50, 100, …, 900 where each value is "R G B".
     */
    public static function generateShades(string $hex): array
    {
        [$r, $g, $b] = static::hexToRgb($hex);

        $blend = static function (float $ratio, array $target) use ($r, $g, $b): string {
            return implode(' ', [
                (int) round($r * (1 - $ratio) + $target[0] * $ratio),
                (int) round($g * (1 - $ratio) + $target[1] * $ratio),
                (int) round($b * (1 - $ratio) + $target[2] * $ratio),
            ]);
        };

        $white  = [255, 255, 255];
        $black  = [10, 10, 10];

        return [
            50  => $blend(0.95, $white),
            100 => $blend(0.90, $white),
            200 => $blend(0.75, $white),
            300 => $blend(0.60, $white),
            400 => $blend(0.35, $white),
            500 => "$r $g $b",
            600 => $blend(0.20, $black),
            700 => $blend(0.40, $black),
            800 => $blend(0.60, $black),
            900 => $blend(0.75, $black),
        ];
    }

    /**
     * WCAG relative luminance for an sRGB triplet (0–255).
     */
    public static function relativeLuminance(int $r, int $g, int $b): float
    {
        $linearise = static function (int $c): float {
            $v = $c / 255;
            return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
        };

        return 0.2126 * $linearise($r)
             + 0.7152 * $linearise($g)
             + 0.0722 * $linearise($b);
    }

    /**
     * Return "255 255 255" (white) or "17 24 39" (near-black) depending on which
     * gives ≥ 4.5:1 contrast ratio against the given background color.
     */
    public static function contrastColor(string $hex): string
    {
        [$r, $g, $b] = static::hexToRgb($hex);
        $lum = static::relativeLuminance($r, $g, $b);
        $contrastWithWhite = 1.05 / ($lum + 0.05);
        return $contrastWithWhite >= 4.5 ? '255 255 255' : '17 24 39';
    }

    // ── CSS var generation ─────────────────────────────────────────────────

    /**
     * Build the :root CSS variable declarations from a settings array.
     * Values are sanitised here; only valid hex colors pass through.
     */
    public static function buildCssVars(array $settings): string
    {
        $primary = static::isValidHex($settings['theme_primary_color'] ?? '')
            ? $settings['theme_primary_color']
            : static::DEFAULTS['theme_primary_color'];

        $accent = static::isValidHex($settings['theme_accent_color'] ?? '')
            ? $settings['theme_accent_color']
            : static::DEFAULTS['theme_accent_color'];

        $primaryShades  = static::generateShades($primary);
        $accentShades   = static::generateShades($accent);
        $primaryContrast = static::contrastColor($primary);

        $lines = [];

        foreach ($primaryShades as $shade => $rgb) {
            $lines[] = "--color-primary-$shade: $rgb;";
        }
        $lines[] = "--color-primary-contrast: $primaryContrast;";

        foreach ($accentShades as $shade => $rgb) {
            $lines[] = "--color-accent-$shade: $rgb;";
        }

        // Corner radius
        $cornerMap = ['sharp' => '0px', 'rounded' => '0.5rem', 'extra-rounded' => '1rem'];
        $corner = $cornerMap[$settings['theme_corner_style'] ?? 'rounded'] ?? '0.5rem';
        $lines[] = "--radius-base: $corner;";

        // Base font size
        $fontMap = ['small' => '14px', 'medium' => '16px', 'large' => '18px'];
        $font = $fontMap[$settings['theme_font_size'] ?? 'medium'] ?? '16px';
        $lines[] = "--font-base: $font;";

        // Density spacing
        $densityMap = [
            'comfortable' => ['btn-y' => '0.5rem',    'btn-x' => '1rem',     'row-y' => '0.75rem'],
            'compact'     => ['btn-y' => '0.25rem',   'btn-x' => '0.75rem',  'row-y' => '0.375rem'],
        ];
        $d = $densityMap[$settings['theme_density'] ?? 'comfortable'] ?? $densityMap['comfortable'];
        $lines[] = "--spacing-btn-y: {$d['btn-y']};";
        $lines[] = "--spacing-btn-x: {$d['btn-x']};";
        $lines[] = "--spacing-row: {$d['row-y']};";

        // Sidebar style
        switch ($settings['theme_sidebar_style'] ?? 'light') {
            case 'dark':
                $lines[] = '--sidebar-bg: 17 24 39;';
                $lines[] = '--sidebar-text: 209 213 219;';
                $lines[] = '--sidebar-border: 31 41 55;';
                $lines[] = '--sidebar-active-bg: 31 41 55;';
                $lines[] = '--sidebar-active-text: 255 255 255;';
                $lines[] = '--sidebar-icon: 107 114 128;';
                $lines[] = '--sidebar-icon-active: ' . $primaryShades[400] . ';';
                break;
            case 'brand':
                $lines[] = '--sidebar-bg: ' . $primaryShades[700] . ';';
                $lines[] = '--sidebar-text: ' . $primaryShades[100] . ';';
                $lines[] = '--sidebar-border: ' . $primaryShades[800] . ';';
                $lines[] = '--sidebar-active-bg: ' . $primaryShades[600] . ';';
                $lines[] = '--sidebar-active-text: ' . $primaryContrast . ';';
                $lines[] = '--sidebar-icon: ' . $primaryShades[300] . ';';
                $lines[] = '--sidebar-icon-active: 255 255 255;';
                break;
            default: // light
                $lines[] = '--sidebar-bg: 255 255 255;';
                $lines[] = '--sidebar-text: 75 85 99;';
                $lines[] = '--sidebar-border: 229 231 235;';
                $lines[] = '--sidebar-active-bg: ' . $primaryShades[50] . ';';
                $lines[] = '--sidebar-active-text: ' . $primaryShades[700] . ';';
                $lines[] = '--sidebar-icon: 156 163 175;';
                $lines[] = '--sidebar-icon-active: ' . $primaryShades[500] . ';';
        }

        return implode("\n        ", $lines);
    }

    // ── Cache ──────────────────────────────────────────────────────────────

    public static function getSettings(): array
    {
        return Cache::remember(static::CACHE_KEY, 3600, function () {
            $keys = array_keys(static::DEFAULTS);
            $rows = Setting::whereIn('key', $keys)->pluck('value', 'key');
            return array_merge(static::DEFAULTS, $rows->toArray());
        });
    }

    public static function getCssVars(): string
    {
        return static::buildCssVars(static::getSettings());
    }

    public static function clearCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
