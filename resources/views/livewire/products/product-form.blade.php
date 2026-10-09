<div x-data="{ tab: 'basic' }" class="space-y-6">

    {{-- Toast --}}
    @if($showToast)
    <div x-data="{ show: true }" x-show="show"
         x-init="setTimeout(() => { show = false; $wire.dismissToast(); }, 3000)"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-4 py-3 rounded-xl shadow-xl text-sm font-medium">
        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $toastMessage }}
    </div>
    @endif

    {{-- Tab bar --}}
    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700">
        @foreach(['basic' => 'Basic Info', 'variants' => 'Variants', 'addons' => 'Add-ons', 'image' => 'Image'] as $slug => $label)
        <button type="button" @click="tab = '{{ $slug }}'"
                :class="tab === '{{ $slug }}'
                    ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'"
                class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors">
            {{ $label }}
            @if($slug === 'variants' && count($variants) > 0)
            <span class="ml-1 text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 px-1.5 py-0.5 rounded-full">{{ count($variants) }}</span>
            @endif
            @if($slug === 'addons' && count($selectedAddonGroupIds) > 0)
            <span class="ml-1 text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 px-1.5 py-0.5 rounded-full">{{ count($selectedAddonGroupIds) }}</span>
            @endif
        </button>
        @endforeach
    </div>

    {{-- ── TAB: Basic Info ─────────────────────────────────────────────────── --}}
    <div x-show="tab === 'basic'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <div class="space-y-4 bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Product Details</h3>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name <span class="text-red-500">*</span></label>
                <input wire:model="name" type="text" placeholder="e.g. Classic Burger"
                       class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Category</label>
                <select wire:model="categoryId"
                        class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">No category</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('categoryId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">SKU</label>
                    <div class="flex gap-1.5">
                        <input wire:model="sku" type="text" placeholder="SKU-XXXXXX"
                               class="flex-1 min-w-0 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                        <button type="button" wire:click="generateSku" title="Auto-generate SKU"
                                class="flex-shrink-0 px-2 py-2 border border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-xs">
                            ↺
                        </button>
                    </div>
                    @error('sku') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Barcode</label>
                    <input wire:model="barcode" type="text" placeholder="Optional"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                <textarea wire:model="description" rows="3" placeholder="Optional description"
                          class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Prep Note (shown on KOT)</label>
                <input wire:model="prepNote" type="text" placeholder="e.g. Cook well done"
                       class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Pricing</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Selling price <span class="text-red-500">*</span></label>
                        <div class="flex items-center border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-primary-500">
                            <span class="px-3 text-gray-500 dark:text-gray-400 text-sm flex-shrink-0 border-r border-gray-200 dark:border-gray-600 py-2">{{ \App\Models\Setting::get('currency_symbol', '$') }}</span>
                            <input wire:model="price" type="number" step="0.01" min="0"
                                   class="flex-1 px-3 py-2 text-sm bg-transparent dark:text-gray-200 focus:outline-none">
                        </div>
                        @error('price') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Cost price</label>
                        <div class="flex items-center border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-primary-500">
                            <span class="px-3 text-gray-500 dark:text-gray-400 text-sm flex-shrink-0 border-r border-gray-200 dark:border-gray-600 py-2">{{ \App\Models\Setting::get('currency_symbol', '$') }}</span>
                            <input wire:model="costPrice" type="number" step="0.01" min="0" placeholder="Optional"
                                   class="flex-1 px-3 py-2 text-sm bg-transparent dark:text-gray-200 focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5 space-y-3">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Status &amp; Options</h3>

                <label class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 cursor-pointer">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Active</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Show in product catalog</p>
                    </div>
                    <input wire:model="isActive" type="checkbox"
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                </label>

                <label class="flex items-center justify-between py-2 border-b border-gray-100 dark:border-gray-700 cursor-pointer">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Available (In Stock)</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Uncheck to mark as sold out</p>
                    </div>
                    <input wire:model="isAvailable" type="checkbox"
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                </label>

                <label class="flex items-center justify-between py-2 cursor-pointer">
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Print on KOT</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Send to kitchen order ticket</p>
                    </div>
                    <input wire:model="printsOnKot" type="checkbox"
                           class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                </label>
            </div>
        </div>
    </div>

    {{-- ── TAB: Variants ───────────────────────────────────────────────────── --}}
    <div x-show="tab === 'variants'" class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Variants</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">e.g. Small / Medium / Large, Single / Double</p>
                </div>
                <button type="button" wire:click="addVariant" class="btn-primary px-3 py-1.5 text-xs font-semibold flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Variant
                </button>
            </div>

            @if(empty($variants))
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">
                No variants. Product will use its base price. Add variants for size/style options.
            </p>
            @else
            <div class="space-y-2">
                <div class="grid grid-cols-12 gap-2 px-1 mb-1">
                    <div class="col-span-5 text-xs font-medium text-gray-500 dark:text-gray-400">Name</div>
                    <div class="col-span-3 text-xs font-medium text-gray-500 dark:text-gray-400">Price</div>
                    <div class="col-span-2 text-xs font-medium text-gray-500 dark:text-gray-400 text-center">Default</div>
                    <div class="col-span-2"></div>
                </div>
                @foreach($variants as $i => $variant)
                <div class="grid grid-cols-12 gap-2 items-center">
                    <div class="col-span-5">
                        <input wire:model="variants.{{ $i }}.name" type="text" placeholder="Variant name"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error("variants.$i.name") <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-3">
                        <input wire:model="variants.{{ $i }}.price" type="number" step="0.01" min="0"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        @error("variants.$i.price") <p class="mt-0.5 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="col-span-2 flex justify-center">
                        <input type="radio" name="default_variant" wire:click="setDefaultVariant({{ $i }})"
                               @checked($variant['is_default'])
                               class="w-4 h-4 border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500 cursor-pointer">
                    </div>
                    <div class="col-span-2 flex justify-end">
                        <button type="button" wire:click="removeVariant({{ $i }})"
                                class="p-1.5 rounded-lg text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── TAB: Add-ons ────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'addons'" class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <div class="mb-4">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Add-on Groups</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Select which add-on groups apply to this product. Manage groups under the Add-ons tab.</p>
            </div>

            @if($addonGroups->isEmpty())
            <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">
                No add-on groups yet. Create some under the <a href="{{ route('products.index', ['tab' => 'addons']) }}" class="text-primary-500 hover:underline">Add-ons tab</a>.
            </p>
            @else
            <div class="space-y-3">
                @foreach($addonGroups as $group)
                <label class="flex items-start gap-3 p-3 rounded-xl border-2 cursor-pointer transition-colors
                              {{ in_array($group->id, $selectedAddonGroupIds)
                                  ? 'border-primary-300 dark:border-primary-700 bg-primary-50 dark:bg-primary-900/20'
                                  : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}">
                    <input type="checkbox" wire:model="selectedAddonGroupIds" value="{{ $group->id }}"
                           class="mt-0.5 w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-white">
                            {{ $group->name }}
                            @if($group->is_required)
                            <span class="ml-1.5 text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-1.5 py-0.5 rounded-full font-medium">Required</span>
                            @endif
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Pick {{ $group->min_selectable }}–{{ $group->max_selectable }} ·
                            {{ $group->options->count() }} option{{ $group->options->count() !== 1 ? 's' : '' }}:
                            {{ $group->options->map(fn($o) => $o->name)->implode(', ') }}
                        </p>
                    </div>
                </label>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- ── TAB: Image ──────────────────────────────────────────────────────── --}}
    <div x-show="tab === 'image'" class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-4">Product Image</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">JPG, PNG or WEBP · max 2 MB · auto-resized to 800×800 with a 200×200 thumbnail</p>

            {{-- Current / preview --}}
            @if($existingImage && ! $removeImage)
            <div class="mb-4 flex items-start gap-4">
                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage) }}"
                     alt="Current image"
                     class="w-32 h-32 object-cover rounded-xl border border-gray-200 dark:border-gray-700">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Current image</p>
                    <button type="button" wire:click="clearImage"
                            class="text-xs px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 transition-colors font-medium">
                        Remove image
                    </button>
                </div>
            </div>
            @elseif($removeImage)
            <div class="mb-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg text-xs text-yellow-700 dark:text-yellow-400">
                Image will be removed on save. Upload a new one below to replace it.
            </div>
            @endif

            {{-- Upload input --}}
            <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-6 text-center hover:border-primary-400 dark:hover:border-primary-600 transition-colors">
                <input wire:model="newImage" type="file" accept=".jpg,.jpeg,.png,.webp"
                       class="hidden" id="img-upload">
                <label for="img-upload" class="cursor-pointer">
                    <svg class="w-8 h-8 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Click to upload or drag and drop</p>
                </label>
            </div>

            @if($newImage)
            <div class="mt-3 flex items-center gap-3">
                <img src="{{ $newImage->temporaryUrl() }}" alt="Preview" class="w-16 h-16 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
                <div>
                    <p class="text-xs font-medium text-gray-700 dark:text-gray-300">{{ $newImage->getClientOriginalName() }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ number_format($newImage->getSize() / 1024, 1) }} KB</p>
                </div>
            </div>
            @endif

            @error('newImage') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Action buttons --}}
    <div class="flex items-center gap-3 pt-2 border-t border-gray-200 dark:border-gray-700">
        <button wire:click="save" class="btn-primary px-6 py-2.5 text-sm font-semibold">
            Save Product
        </button>
        <button wire:click="cancel" class="btn-secondary px-6 py-2.5 text-sm font-semibold">
            Cancel
        </button>
    </div>

</div>
