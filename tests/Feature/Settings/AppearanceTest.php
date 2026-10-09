<?php

namespace Tests\Feature\Settings;

use App\Livewire\Settings\Appearance;
use App\Models\Setting;
use App\Models\User;
use App\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use RefreshDatabase;

    // ── Access control ─────────────────────────────────────────────────────

    public function test_guest_cannot_access_settings(): void
    {
        $this->get(route('settings.index', ['tab' => 'appearance']))
            ->assertRedirect(route('login'));
    }

    public function test_cashier_cannot_access_settings(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->actingAs($cashier)
            ->get(route('settings.index', ['tab' => 'appearance']))
            ->assertForbidden();
    }

    public function test_kitchen_cannot_access_settings(): void
    {
        $kitchen = User::factory()->create(['role' => 'kitchen']);
        $this->actingAs($kitchen)
            ->get(route('settings.index', ['tab' => 'appearance']))
            ->assertForbidden();
    }

    public function test_admin_can_access_appearance_settings(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->get(route('settings.index', ['tab' => 'appearance']))
            ->assertOk()
            ->assertSeeLivewire(Appearance::class);
    }

    // ── Validation ─────────────────────────────────────────────────────────

    public function test_invalid_hex_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->set('primaryColor', 'not-a-color')
            ->call('save')
            ->assertHasErrors(['primaryColor']);
    }

    public function test_hex_without_hash_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->set('primaryColor', 'f97316')
            ->call('save')
            ->assertHasErrors(['primaryColor']);
    }

    public function test_xss_attempt_in_color_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->set('primaryColor', '</style><script>alert(1)</script>')
            ->call('save')
            ->assertHasErrors(['primaryColor']);
    }

    public function test_valid_hex_saves_successfully(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->set('primaryColor', '#2563eb')
            ->set('accentColor', '#06b6d4')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', ['key' => 'theme_primary_color', 'value' => '#2563eb']);
        $this->assertDatabaseHas('settings', ['key' => 'theme_accent_color', 'value' => '#06b6d4']);
    }

    // ── Saved colors appear in layout ──────────────────────────────────────

    public function test_saved_primary_color_appears_in_css_vars_in_layout(): void
    {
        Setting::set('theme_primary_color', '#2563eb');
        ThemeService::clearCache();

        $admin = User::factory()->create(['role' => 'admin']);
        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertOk();
        // The style block in the layout should contain CSS vars derived from #2563eb (R=37 G=99 B=235)
        $response->assertSee('--color-primary-500: 37 99 235', false);
    }

    public function test_saved_color_appears_in_login_page_css_vars(): void
    {
        Setting::set('theme_primary_color', '#dc2626');
        ThemeService::clearCache();

        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertSee('--color-primary-500: 220 38 38', false);
    }

    // ── Reset to defaults ──────────────────────────────────────────────────

    public function test_reset_restores_default_primary_color(): void
    {
        Setting::set('theme_primary_color', '#2563eb');
        ThemeService::clearCache();

        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->call('resetToDefaults')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('settings', [
            'key'   => 'theme_primary_color',
            'value' => ThemeService::DEFAULTS['theme_primary_color'],
        ]);
    }

    public function test_reset_clears_theme_cache(): void
    {
        // Prime the cache
        ThemeService::getSettings();
        $this->assertTrue(cache()->has(ThemeService::CACHE_KEY));

        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->call('resetToDefaults');

        $this->assertFalse(cache()->has(ThemeService::CACHE_KEY));
    }

    // ── Cache is cleared on save ────────────────────────────────────────────

    public function test_cache_is_cleared_after_save(): void
    {
        ThemeService::getSettings();
        $this->assertTrue(cache()->has(ThemeService::CACHE_KEY));

        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->set('primaryColor', '#7c3aed')
            ->call('save');

        $this->assertFalse(cache()->has(ThemeService::CACHE_KEY));
    }

    // ── Preset application ─────────────────────────────────────────────────

    public function test_applying_preset_updates_colors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->call('applyPreset', 'ocean-blue')
            ->assertSet('primaryColor', '#2563eb')
            ->assertSet('accentColor', '#06b6d4');
    }

    public function test_applying_invalid_preset_is_ignored(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $original = ThemeService::DEFAULTS['theme_primary_color'];

        Livewire::actingAs($admin)
            ->test(Appearance::class)
            ->call('applyPreset', 'nonexistent-preset')
            ->assertSet('primaryColor', $original);
    }
}
