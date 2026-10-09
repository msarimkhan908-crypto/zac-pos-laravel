<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Services\ThemeService;
use Livewire\Attributes\Validate;
use Livewire\Component;

class Appearance extends Component
{
    #[Validate('required|regex:/^#[0-9a-fA-F]{6}$/')]
    public string $primaryColor = '#f97316';

    #[Validate('required|regex:/^#[0-9a-fA-F]{6}$/')]
    public string $accentColor = '#fbbf24';

    #[Validate('required|in:light,dark,system')]
    public string $defaultMode = 'light';

    #[Validate('required|in:sharp,rounded,extra-rounded')]
    public string $cornerStyle = 'rounded';

    #[Validate('required|in:light,dark,brand')]
    public string $sidebarStyle = 'light';

    #[Validate('required|in:small,medium,large')]
    public string $fontSize = 'medium';

    #[Validate('required|in:comfortable,compact')]
    public string $density = 'comfortable';

    public bool $showToast = false;
    public string $toastMessage = '';

    public function mount(): void
    {
        $settings = ThemeService::getSettings();
        $this->primaryColor  = $settings['theme_primary_color'];
        $this->accentColor   = $settings['theme_accent_color'];
        $this->defaultMode   = $settings['theme_default_mode'];
        $this->cornerStyle   = $settings['theme_corner_style'];
        $this->sidebarStyle  = $settings['theme_sidebar_style'];
        $this->fontSize      = $settings['theme_font_size'];
        $this->density       = $settings['theme_density'];
    }

    public function applyPreset(string $key): void
    {
        $preset = ThemeService::PRESETS[$key] ?? null;
        if (! $preset) {
            return;
        }
        $this->primaryColor = $preset['primary'];
        $this->accentColor  = $preset['accent'];
    }

    public function save(): void
    {
        $this->validate();

        $map = [
            'theme_primary_color' => $this->primaryColor,
            'theme_accent_color'  => $this->accentColor,
            'theme_default_mode'  => $this->defaultMode,
            'theme_corner_style'  => $this->cornerStyle,
            'theme_sidebar_style' => $this->sidebarStyle,
            'theme_font_size'     => $this->fontSize,
            'theme_density'       => $this->density,
        ];

        foreach ($map as $key => $value) {
            Setting::set($key, $value);
        }

        ThemeService::clearCache();

        $this->showToast   = true;
        $this->toastMessage = 'Appearance settings saved.';
    }

    public function resetToDefaults(): void
    {
        $defaults = ThemeService::DEFAULTS;

        $this->primaryColor  = $defaults['theme_primary_color'];
        $this->accentColor   = $defaults['theme_accent_color'];
        $this->defaultMode   = $defaults['theme_default_mode'];
        $this->cornerStyle   = $defaults['theme_corner_style'];
        $this->sidebarStyle  = $defaults['theme_sidebar_style'];
        $this->fontSize      = $defaults['theme_font_size'];
        $this->density       = $defaults['theme_density'];

        foreach ($defaults as $key => $value) {
            Setting::set($key, $value);
        }

        ThemeService::clearCache();

        $this->showToast    = true;
        $this->toastMessage = 'Settings reset to defaults.';
    }

    public function dismissToast(): void
    {
        $this->showToast = false;
    }

    public function render()
    {
        return view('livewire.settings.appearance', [
            'presets' => ThemeService::PRESETS,
        ]);
    }
}
