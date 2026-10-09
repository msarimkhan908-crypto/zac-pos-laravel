<div class="space-y-4">

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

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
        <div class="flex flex-col sm:flex-row gap-2 flex-1 w-full sm:w-auto">
            {{-- Search --}}
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text"
                       placeholder="Search name, SKU, barcode…"
                       class="w-full pl-9 pr-3 py-2 text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            {{-- Category filter --}}
            <select wire:model.live="categoryFilter"
                    class="text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>

            {{-- Status filter --}}
            <select wire:model.live="statusFilter"
                    class="text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all">All status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>

            {{-- Availability filter --}}
            <select wire:model.live="availabilityFilter"
                    class="text-sm border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all">All availability</option>
                <option value="available">Available</option>
                <option value="soldout">Sold out</option>
            </select>
        </div>

        <div class="flex items-center gap-2 flex-shrink-0">
            {{-- View toggle --}}
            <div class="flex border border-gray-300 dark:border-gray-600 rounded-lg overflow-hidden">
                <button wire:click="$set('view', 'table')"
                        class="p-2 transition-colors {{ $view === 'table' ? 'bg-primary-500 text-white' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 14h18M10 4v16"/>
                    </svg>
                </button>
                <button wire:click="$set('view', 'grid')"
                        class="p-2 transition-colors {{ $view === 'grid' ? 'bg-primary-500 text-white' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M3 3h7v7H3V3zm0 11h7v7H3v-7zm11-11h7v7h-7V3zm0 11h7v7h-7v-7z"/>
                    </svg>
                </button>
            </div>

            <a href="{{ route('products.create') }}" class="btn-primary px-4 py-2 text-sm font-semibold flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New Product
            </a>
        </div>
    </div>

    {{-- Bulk action bar --}}
    @if(count($selectedIds) > 0)
    <div class="flex items-center gap-3 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-xl px-4 py-2.5">
        <span class="text-sm font-medium text-primary-700 dark:text-primary-300">{{ count($selectedIds) }} selected</span>
        <div class="flex gap-2 ml-2">
            <button wire:click="bulkActivate" class="text-xs px-3 py-1.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg hover:bg-green-200 font-medium transition-colors">Activate</button>
            <button wire:click="bulkDeactivate" class="text-xs px-3 py-1.5 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 font-medium transition-colors">Deactivate</button>
            <button wire:click="bulkMarkSoldOut" class="text-xs px-3 py-1.5 bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 rounded-lg hover:bg-yellow-200 font-medium transition-colors">Sold Out</button>
            <button wire:click="bulkDelete"
                    wire:confirm="Delete {{ count($selectedIds) }} product(s)? This cannot be undone."
                    class="text-xs px-3 py-1.5 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg hover:bg-red-200 font-medium transition-colors">Delete</button>
        </div>
        <button wire:click="$set('selectedIds', [])" class="ml-auto text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">Clear</button>
    </div>
    @endif

    {{-- Empty state --}}
    @if($products->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ $search || $categoryFilter || $statusFilter !== 'all' || $availabilityFilter !== 'all'
                ? 'No products match your filters.'
                : 'No products yet. Create one to get started.' }}
        </p>
    </div>

    @elseif($view === 'grid')
    {{-- Grid view --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($products as $product)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden group hover:shadow-md transition-shadow">
            <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
                @if($product->image_url)
                <img src="{{ $product->thumb_url }}" alt="{{ $product->name }}"
                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-10 h-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
                @if(! $product->is_available)
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                    <span class="text-white text-xs font-bold bg-black/60 px-2 py-1 rounded">SOLD OUT</span>
                </div>
                @endif
            </div>
            <div class="p-3">
                <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $product->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $product->category?->name ?? '—' }}</p>
                <p class="text-sm font-bold" style="color: rgb(var(--color-primary-600))">{{ currency($product->price) }}</p>
                <div class="flex gap-1.5 mt-2">
                    <a href="{{ route('products.edit', $product->id) }}"
                       class="flex-1 text-center text-xs py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded font-medium hover:bg-primary-100 transition-colors">Edit</a>
                    <button wire:click="delete('{{ $product->id }}')"
                            wire:confirm="Delete {{ $product->name }}?"
                            class="text-xs px-2 py-1 bg-red-50 dark:bg-red-900/20 text-red-500 dark:text-red-400 rounded hover:bg-red-100 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @else
    {{-- Table view --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="px-4 py-3 text-left w-8">
                        <input type="checkbox" wire:model.live="selectAll"
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden md:table-cell">Category</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden lg:table-cell">SKU</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Price</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Active</th>
                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Available</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($products as $product)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors {{ in_array($product->id, $selectedIds) ? 'bg-primary-50/50 dark:bg-primary-900/10' : '' }}">
                    <td class="px-4 py-3">
                        <input type="checkbox" wire:model.live="selectedIds" value="{{ $product->id }}"
                               class="rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg bg-gray-100 dark:bg-gray-700 flex-shrink-0 overflow-hidden">
                                @if($product->thumb_url)
                                <img src="{{ $product->thumb_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white">{{ $product->name }}</p>
                                @if($product->barcode)
                                <p class="text-xs text-gray-400">{{ $product->barcode }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300 hidden md:table-cell">
                        {{ $product->category?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs hidden lg:table-cell">
                        {{ $product->sku ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white">
                        {{ currency($product->price) }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleActive('{{ $product->id }}')"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold transition-colors
                                       {{ $product->is_active
                                           ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200'
                                           : 'bg-gray-100 dark:bg-gray-700 text-gray-500 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $product->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                            {{ $product->is_active ? 'On' : 'Off' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleAvailable('{{ $product->id }}')"
                                class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-semibold transition-colors
                                       {{ $product->is_available
                                           ? 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 hover:bg-blue-200'
                                           : 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 hover:bg-orange-200' }}">
                            {{ $product->is_available ? 'In Stock' : 'Sold Out' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <button wire:click="duplicate('{{ $product->id }}')" title="Duplicate"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </button>
                            <a href="{{ route('products.edit', $product->id) }}" title="Edit"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button wire:click="delete('{{ $product->id }}')"
                                    wire:confirm="Delete {{ $product->name }}? This cannot be undone."
                                    title="Delete"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="flex justify-end">
        {{ $products->links() }}
    </div>
    @endif

</div>
