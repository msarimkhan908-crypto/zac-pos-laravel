<?php

namespace Tests\Unit;

use App\Services\ThemeService;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ThemeServiceTest extends TestCase
{
    // ── isValidHex ─────────────────────────────────────────────────────────

    public function test_valid_hex_passes(): void
    {
        $this->assertTrue(ThemeService::isValidHex('#f97316'));
        $this->assertTrue(ThemeService::isValidHex('#FFFFFF'));
        $this->assertTrue(ThemeService::isValidHex('#000000'));
        $this->assertTrue(ThemeService::isValidHex('#a1b2c3'));
    }

    public function test_invalid_hex_fails(): void
    {
        $this->assertFalse(ThemeService::isValidHex('f97316'));       // missing #
        $this->assertFalse(ThemeService::isValidHex('#f9731'));       // 5 chars
        $this->assertFalse(ThemeService::isValidHex('#f973166'));     // 7 chars
        $this->assertFalse(ThemeService::isValidHex('#xyz123'));      // invalid chars
        $this->assertFalse(ThemeService::isValidHex(''));
        $this->assertFalse(ThemeService::isValidHex('<script>'));     // injection attempt
    }

    // ── generateShades ─────────────────────────────────────────────────────

    public function test_generate_shades_returns_ten_entries(): void
    {
        $shades = ThemeService::generateShades('#f97316');
        $this->assertCount(10, $shades);
    }

    public function test_generate_shades_has_expected_keys(): void
    {
        $shades = ThemeService::generateShades('#f97316');
        foreach ([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $step) {
            $this->assertArrayHasKey($step, $shades, "Missing shade key $step");
        }
    }

    public function test_generate_shades_values_are_valid_rgb_strings(): void
    {
        $shades = ThemeService::generateShades('#2563eb');
        foreach ($shades as $step => $rgb) {
            $parts = explode(' ', $rgb);
            $this->assertCount(3, $parts, "Shade $step should have exactly 3 components");
            foreach ($parts as $component) {
                $this->assertIsNumeric($component, "Component must be numeric in shade $step");
                $int = (int) $component;
                $this->assertGreaterThanOrEqual(0, $int, "Component out of range in shade $step");
                $this->assertLessThanOrEqual(255, $int, "Component out of range in shade $step");
            }
        }
    }

    public function test_shade_500_matches_base_color(): void
    {
        $shades = ThemeService::generateShades('#dc2626');
        $this->assertSame('220 38 38', $shades[500]);
    }

    public function test_shade_50_is_lighter_than_500(): void
    {
        $shades   = ThemeService::generateShades('#16a34a');
        $sum50    = array_sum(explode(' ', $shades[50]));
        $sum500   = array_sum(explode(' ', $shades[500]));
        $this->assertGreaterThan($sum500, $sum50, 'shade-50 should be lighter (higher sum) than shade-500');
    }

    public function test_shade_900_is_darker_than_500(): void
    {
        $shades   = ThemeService::generateShades('#16a34a');
        $sum900   = array_sum(explode(' ', $shades[900]));
        $sum500   = array_sum(explode(' ', $shades[500]));
        $this->assertLessThan($sum500, $sum900, 'shade-900 should be darker (lower sum) than shade-500');
    }

    // ── contrastColor ──────────────────────────────────────────────────────

    public function test_contrast_is_white_for_very_dark_primary(): void
    {
        // #111827 (gray-900) is very dark → white text gives ≥ 4.5:1 contrast
        $this->assertSame('255 255 255', ThemeService::contrastColor('#111827'));
    }

    public function test_contrast_is_dark_for_very_light_primary(): void
    {
        // #fef9ee is very light → dark text needed
        $this->assertSame('17 24 39', ThemeService::contrastColor('#fef9ee'));
    }

    public function test_contrast_is_dark_for_bright_yellow(): void
    {
        // Yellow is high-luminance → dark text
        $this->assertSame('17 24 39', ThemeService::contrastColor('#fbbf24'));
    }

    // ── relativeLuminance ──────────────────────────────────────────────────

    public function test_white_has_max_luminance(): void
    {
        $lum = ThemeService::relativeLuminance(255, 255, 255);
        $this->assertEqualsWithDelta(1.0, $lum, 0.001);
    }

    public function test_black_has_zero_luminance(): void
    {
        $lum = ThemeService::relativeLuminance(0, 0, 0);
        $this->assertEqualsWithDelta(0.0, $lum, 0.001);
    }

    // ── buildCssVars ───────────────────────────────────────────────────────

    public function test_build_css_vars_contains_all_primary_shades(): void
    {
        $css = ThemeService::buildCssVars(ThemeService::DEFAULTS);
        foreach ([50, 100, 200, 300, 400, 500, 600, 700, 800, 900] as $step) {
            $this->assertStringContainsString("--color-primary-$step:", $css);
        }
    }

    public function test_build_css_vars_contains_contrast_var(): void
    {
        $css = ThemeService::buildCssVars(ThemeService::DEFAULTS);
        $this->assertStringContainsString('--color-primary-contrast:', $css);
    }

    public function test_build_css_vars_contains_sidebar_vars(): void
    {
        $css = ThemeService::buildCssVars(ThemeService::DEFAULTS);
        $this->assertStringContainsString('--sidebar-bg:', $css);
        $this->assertStringContainsString('--sidebar-text:', $css);
        $this->assertStringContainsString('--sidebar-border:', $css);
    }

    public function test_build_css_vars_falls_back_to_default_for_invalid_hex(): void
    {
        $settings = array_merge(ThemeService::DEFAULTS, ['theme_primary_color' => 'not-valid']);
        $css      = ThemeService::buildCssVars($settings);
        // Should still contain primary vars (using default color)
        $this->assertStringContainsString('--color-primary-500:', $css);
    }

    public function test_build_css_vars_rejects_injection_attempt(): void
    {
        $malicious = array_merge(ThemeService::DEFAULTS, [
            'theme_primary_color' => '</style><script>alert(1)</script>',
        ]);
        $css = ThemeService::buildCssVars($malicious);
        // Falls back to default; no script tag should appear
        $this->assertStringNotContainsString('<script>', $css);
    }

    // ── Reset defaults completeness ─────────────────────────────────────────

    public function test_defaults_cover_all_required_keys(): void
    {
        $required = [
            'theme_primary_color', 'theme_accent_color', 'theme_default_mode',
            'theme_corner_style', 'theme_sidebar_style', 'theme_font_size', 'theme_density',
        ];
        foreach ($required as $key) {
            $this->assertArrayHasKey($key, ThemeService::DEFAULTS, "Missing default for $key");
        }
    }
}
