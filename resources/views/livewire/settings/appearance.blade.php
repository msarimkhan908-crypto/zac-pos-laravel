<div
    x-data="appearancePreview(@js($primaryColor), @js($accentColor), @js($cornerStyle), @js($fontSize), @js($density), @js($sidebarStyle))"
    x-init="init()"
    class="space-y-6">

    {{-- Toast --}}
    @if($showToast)
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => { show = false; $wire.dismissToast(); }, 3500)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-3 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-4 py-3 rounded-xl shadow-xl text-sm font-medium">
        <svg class="w-5 h-5 text-green-400 dark:text-green-600 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $toastMessage }}
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ── Controls (left 2 cols) ─────────────────────────────── --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Color presets --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Color Presets</h3>
                <div class="flex flex-wrap gap-3">
                    @foreach($presets as $key => $preset)
                    <button type="button"
                            wire:click="applyPreset('{{ $key }}')"
                            x-on:click="applyPreset('{{ $preset['primary'] }}', '{{ $preset['accent'] }}')"
                            class="flex items-center gap-2 px-3 py-2 rounded-lg border-2 text-xs font-medium transition-colors
                                   {{ $primaryColor === $preset['primary'] ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-600 hover:border-gray-300 dark:hover:border-gray-500' }}
                                   text-gray-700 dark:text-gray-300">
                        <span class="w-5 h-5 rounded-full border border-black/10 shadow-sm flex-shrink-0"
                              style="background-color: {{ $preset['primary'] }}"></span>
                        {{ $preset['label'] }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Custom colors --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Custom Colors</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Primary color</label>
                        <div class="flex items-center gap-2">
                            <input type="color"
                                   wire:model.live="primaryColor"
                                   x-on:input="updatePrimary($event.target.value)"
                                   value="{{ $primaryColor }}"
                                   class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                            <input type="text"
                                   wire:model.live="primaryColor"
                                   x-on:input="if (/^#[0-9a-fA-F]{6}$/.test($event.target.value)) updatePrimary($event.target.value)"
                                   placeholder="#f97316"
                                   maxlength="7"
                                   class="flex-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        @error('primaryColor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Accent color</label>
                        <div class="flex items-center gap-2">
                            <input type="color"
                                   wire:model.live="accentColor"
                                   x-on:input="updateAccent($event.target.value)"
                                   value="{{ $accentColor }}"
                                   class="w-10 h-10 rounded-lg border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                            <input type="text"
                                   wire:model.live="accentColor"
                                   x-on:input="if (/^#[0-9a-fA-F]{6}$/.test($event.target.value)) updateAccent($event.target.value)"
                                   placeholder="#fbbf24"
                                   maxlength="7"
                                   class="flex-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        @error('accentColor') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Display options --}}
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Display Options</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                    {{-- Default mode --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Default mode for new users</label>
                        <select wire:model="defaultMode"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="system">System</option>
                        </select>
                    </div>

                    {{-- Corner style --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Corner style</label>
                        <div class="flex gap-2">
                            @foreach(['sharp' => 'Sharp', 'rounded' => 'Rounded', 'extra-rounded' => 'Extra'] as $val => $label)
                            <button type="button"
                                    wire:click="$set('cornerStyle', '{{ $val }}')"
                                    x-on:click="updateCorner('{{ $val }}')"
                                    class="flex-1 py-2 text-xs font-medium border-2 transition-colors
                                           {{ $cornerStyle === $val ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300' }}
                                           {{ $val === 'sharp' ? 'rounded-none' : ($val === 'rounded' ? 'rounded-md' : 'rounded-xl') }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Sidebar style --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Sidebar style</label>
                        <select wire:model="sidebarStyle"
                                x-on:change="updateSidebar($event.target.value)"
                                class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <option value="light">Light</option>
                            <option value="dark">Dark</option>
                            <option value="brand">Brand color</option>
                        </select>
                    </div>

                    {{-- Font size --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Base font size</label>
                        <div class="flex gap-2">
                            @foreach(['small' => 'S', 'medium' => 'M', 'large' => 'L'] as $val => $label)
                            <button type="button"
                                    wire:click="$set('fontSize', '{{ $val }}')"
                                    class="flex-1 py-2 text-xs font-semibold rounded-lg border-2 transition-colors
                                           {{ $fontSize === $val ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300' }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Density --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-2">Density</label>
                        <div class="flex gap-3">
                            @foreach(['comfortable' => 'Comfortable', 'compact' => 'Compact'] as $val => $label)
                            <button type="button"
                                    wire:click="$set('density', '{{ $val }}')"
                                    x-on:click="updateDensity('{{ $val }}')"
                                    class="flex-1 py-2.5 text-sm font-medium rounded-lg border-2 transition-colors
                                           {{ $density === $val ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300' : 'border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:border-gray-300' }}">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-3">
                <button type="button" wire:click="save"
                        class="btn-primary px-6 py-2.5 text-sm font-semibold">
                    Save changes
                </button>
                <button type="button" wire:click="resetToDefaults"
                        wire:confirm="Reset all appearance settings to defaults?"
                        class="btn-secondary px-6 py-2.5 text-sm font-semibold">
                    Reset to defaults
                </button>
            </div>

        </div>

        {{-- ── Live preview (right col) ────────────────────────────── --}}
        <div class="xl:col-span-1">
            <div class="sticky top-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Live Preview</h3>
                <div id="theme-preview" class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm">

                    {{-- Preview sidebar strip --}}
                    <div class="flex">
                        <div id="preview-sidebar"
                             class="w-28 p-3 flex flex-col gap-1.5 text-xs border-r"
                             style="background-color: rgb(var(--preview-sidebar-bg, var(--sidebar-bg))); border-color: rgb(var(--preview-sidebar-border, var(--sidebar-border)))">
                            @foreach(['POS', 'Orders', 'Settings'] as $item)
                            <div class="px-2 py-1.5 rounded flex items-center gap-1.5 font-medium
                                        {{ $loop->first ? 'preview-sidebar-active' : '' }}"
                                 style="{{ $loop->first ? 'background-color: rgb(var(--preview-active-bg)); color: rgb(var(--preview-active-text));' : 'color: rgb(var(--preview-sidebar-text, var(--sidebar-text)));' }}">
                                <span class="w-2 h-2 rounded-full inline-block"
                                      style="{{ $loop->first ? 'background-color: rgb(var(--preview-icon-active));' : 'background-color: rgb(var(--preview-sidebar-icon, var(--sidebar-icon)));' }}"></span>
                                {{ $item }}
                            </div>
                            @endforeach
                        </div>

                        {{-- Preview content area --}}
                        <div class="flex-1 p-4 bg-gray-50 dark:bg-gray-900 space-y-4">

                            {{-- Buttons --}}
                            <div class="flex flex-wrap gap-2">
                                <button class="btn-primary text-xs px-3 py-1.5 font-semibold"
                                        style="background-color: rgb(var(--preview-p500, var(--color-primary-500))); color: rgb(var(--preview-contrast, var(--color-primary-contrast))); border-radius: var(--preview-radius, var(--radius-base))">
                                    Primary
                                </button>
                                <button class="btn-secondary text-xs px-3 py-1.5 font-semibold"
                                        style="color: rgb(var(--preview-p600, var(--color-primary-600))); border-color: rgb(var(--preview-p300, var(--color-primary-300))); border-radius: var(--preview-radius, var(--radius-base)); border: 1px solid; background: transparent">
                                    Secondary
                                </button>
                            </div>

                            {{-- Card --}}
                            <div class="bg-white dark:bg-gray-800 p-3 shadow-sm"
                                 style="border-radius: var(--preview-radius, var(--radius-base))">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">Today's Sales</span>
                                    <span class="badge-primary text-xs"
                                          style="background-color: rgb(var(--preview-p100, var(--color-primary-100))); color: rgb(var(--preview-p700, var(--color-primary-700))); border-radius: 9999px; padding: 0.1rem 0.5rem;">
                                        Live
                                    </span>
                                </div>
                                <p class="text-lg font-bold" style="color: rgb(var(--preview-p600, var(--color-primary-600)))">$1,248</p>
                            </div>

                            {{-- Table row --}}
                            <div class="bg-white dark:bg-gray-800 divide-y divide-gray-100 dark:divide-gray-700 overflow-hidden"
                                 style="border-radius: var(--preview-radius, var(--radius-base))">
                                @foreach(['Order #001', 'Order #002'] as $row)
                                <div class="flex items-center justify-between px-3"
                                     style="padding-top: var(--preview-row, var(--spacing-row)); padding-bottom: var(--preview-row, var(--spacing-row))">
                                    <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">{{ $row }}</span>
                                    <span class="text-xs font-bold" style="color: rgb(var(--preview-p500, var(--color-primary-500)))">$24.00</span>
                                </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2 text-center">Preview updates as you change settings</p>
            </div>
        </div>
    </div>
</div>

<script>
function appearancePreview(initialPrimary, initialAccent, initialCorner, initialFont, initialDensity, initialSidebar) {
    return {
        primary: initialPrimary,
        accent:  initialAccent,
        corner:  initialCorner,
        density: initialDensity,
        sidebar: initialSidebar,

        init() {
            this.updatePrimary(this.primary);
            this.updateAccent(this.accent);
            this.updateCorner(this.corner);
            this.updateDensity(this.density);
            this.updateSidebar(this.sidebar);
        },

        hexToRgb(hex) {
            hex = hex.replace('#', '');
            return [
                parseInt(hex.substring(0, 2), 16),
                parseInt(hex.substring(2, 4), 16),
                parseInt(hex.substring(4, 6), 16),
            ];
        },

        generateShades(hex) {
            const [r, g, b] = this.hexToRgb(hex);
            const blend = (ratio, tr, tg, tb) => [
                Math.round(r * (1 - ratio) + tr * ratio),
                Math.round(g * (1 - ratio) + tg * ratio),
                Math.round(b * (1 - ratio) + tb * ratio),
            ].join(' ');
            return {
                50:  blend(0.95, 255, 255, 255),
                100: blend(0.90, 255, 255, 255),
                200: blend(0.75, 255, 255, 255),
                300: blend(0.60, 255, 255, 255),
                400: blend(0.35, 255, 255, 255),
                500: `${r} ${g} ${b}`,
                600: blend(0.20, 10, 10, 10),
                700: blend(0.40, 10, 10, 10),
                800: blend(0.60, 10, 10, 10),
                900: blend(0.75, 10, 10, 10),
            };
        },

        contrastColor(hex) {
            const [r, g, b] = this.hexToRgb(hex);
            const lin = c => { const v = c / 255; return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4); };
            const L = 0.2126 * lin(r) + 0.7152 * lin(g) + 0.0722 * lin(b);
            return 1.05 / (L + 0.05) >= 4.5 ? '255 255 255' : '17 24 39';
        },

        setCssVar(name, value) {
            document.getElementById('theme-preview').style.setProperty(name, value);
        },

        updatePrimary(hex) {
            if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;
            this.primary = hex;
            const shades = this.generateShades(hex);
            const contrast = this.contrastColor(hex);
            Object.entries(shades).forEach(([s, v]) => this.setCssVar(`--preview-p${s}`, v));
            this.setCssVar('--preview-contrast', contrast);
            this.updateSidebar(this.sidebar);
        },

        updateAccent(hex) {
            if (!/^#[0-9a-fA-F]{6}$/.test(hex)) return;
            this.accent = hex;
        },

        updateCorner(val) {
            this.corner = val;
            const map = { sharp: '0px', rounded: '0.5rem', 'extra-rounded': '1rem' };
            this.setCssVar('--preview-radius', map[val] || '0.5rem');
        },

        updateDensity(val) {
            this.density = val;
            this.setCssVar('--preview-row', val === 'compact' ? '0.375rem' : '0.75rem');
        },

        updateSidebar(val) {
            this.sidebar = val;
            const shades = this.generateShades(this.primary);
            const contrast = this.contrastColor(this.primary);
            if (val === 'dark') {
                this.setCssVar('--preview-sidebar-bg', '17 24 39');
                this.setCssVar('--preview-sidebar-text', '209 213 219');
                this.setCssVar('--preview-sidebar-border', '31 41 55');
                this.setCssVar('--preview-active-bg', '31 41 55');
                this.setCssVar('--preview-active-text', '255 255 255');
                this.setCssVar('--preview-sidebar-icon', '107 114 128');
                this.setCssVar('--preview-icon-active', shades[400]);
            } else if (val === 'brand') {
                this.setCssVar('--preview-sidebar-bg', shades[700]);
                this.setCssVar('--preview-sidebar-text', shades[100]);
                this.setCssVar('--preview-sidebar-border', shades[800]);
                this.setCssVar('--preview-active-bg', shades[600]);
                this.setCssVar('--preview-active-text', contrast);
                this.setCssVar('--preview-sidebar-icon', shades[300]);
                this.setCssVar('--preview-icon-active', '255 255 255');
            } else {
                this.setCssVar('--preview-sidebar-bg', '255 255 255');
                this.setCssVar('--preview-sidebar-text', '75 85 99');
                this.setCssVar('--preview-sidebar-border', '229 231 235');
                this.setCssVar('--preview-active-bg', shades[50]);
                this.setCssVar('--preview-active-text', shades[700]);
                this.setCssVar('--preview-sidebar-icon', '156 163 175');
                this.setCssVar('--preview-icon-active', shades[500]);
            }
        },

        applyPreset(primary, accent) {
            this.updatePrimary(primary);
            this.updateAccent(accent);
        },
    };
}
</script>
