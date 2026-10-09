<div class="space-y-4">

    {{-- Toast --}}
    @if($showToast)
    <div x-data="{ show: true }" x-show="show"
         x-init="setTimeout(() => { show = false; $wire.dismissToast(); }, 3000)"
         x-transition:enter="transition ease-out duration-200"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 right-6 z-50 flex items-center gap-2 bg-gray-900 dark:bg-gray-100 text-white dark:text-gray-900 px-4 py-3 rounded-xl shadow-xl text-sm font-medium">
        <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        {{ $toastMessage }}
    </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Categories</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $categories->count() }} total</p>
        </div>
        <button wire:click="openCreate" class="btn-primary px-4 py-2 text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Category
        </button>
    </div>

    {{-- Delete confirmation --}}
    @if($confirmDeleteId)
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
        @if($deleteError)
            <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-3">{{ $deleteError }}</p>
            <button wire:click="cancelDelete" class="btn-secondary px-4 py-1.5 text-sm">Close</button>
        @else
            <p class="text-sm font-medium text-red-700 dark:text-red-300 mb-3">Delete this category? This cannot be undone.</p>
            <div class="flex gap-2">
                <button wire:click="delete" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 text-sm font-semibold rounded-lg transition-colors">
                    Delete
                </button>
                <button wire:click="cancelDelete" class="btn-secondary px-4 py-1.5 text-sm">Cancel</button>
            </div>
        @endif
    </div>
    @endif

    {{-- Table --}}
    @if($categories->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7h18M3 12h18M3 17h12"/>
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">No categories yet. Create one to get started.</p>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700">
                <tr>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Name</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide hidden sm:table-cell">Products</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Status</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Order</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wide">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @foreach($categories as $category)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($category->color)
                            <span class="w-3 h-3 rounded-full flex-shrink-0 border border-black/10"
                                  style="background-color: {{ $category->color }}"></span>
                            @endif
                            <div>
                                <p class="font-medium text-gray-800 dark:text-white">{{ $category->name }}</p>
                                @if($category->description)
                                <p class="text-xs text-gray-400 dark:text-gray-500 truncate max-w-xs">{{ $category->description }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-600 dark:text-gray-300 hidden sm:table-cell">
                        {{ $category->products_count }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <button wire:click="toggleActive('{{ $category->id }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold transition-colors
                                       {{ $category->is_active
                                           ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50'
                                           : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $category->is_active ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                            {{ $category->is_active ? 'Active' : 'Inactive' }}
                        </button>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-1">
                            <button wire:click="moveUp('{{ $category->id }}')"
                                    class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    title="Move up">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                                </svg>
                            </button>
                            <button wire:click="moveDown('{{ $category->id }}')"
                                    class="p-1 rounded text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                    title="Move down">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button wire:click="openEdit('{{ $category->id }}')"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors"
                                    title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="confirmDelete('{{ $category->id }}')"
                                    class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors"
                                    title="Delete">
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

    {{-- Create/Edit Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-40 flex items-center justify-center p-4"
         x-data x-init="$el.focus()" tabindex="-1" @keydown.escape.window="$wire.set('showModal', false)">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-md p-6 z-50">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-5">
                {{ $editingId ? 'Edit Category' : 'New Category' }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. Burgers"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                    <textarea wire:model="description" rows="2" placeholder="Optional description"
                              class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Color (for POS)</label>
                        <div class="flex items-center gap-2">
                            <input wire:model="color" type="color"
                                   value="{{ $color ?? '#f97316' }}"
                                   class="w-10 h-9 rounded border border-gray-300 dark:border-gray-600 cursor-pointer p-0.5">
                            <input wire:model="color" type="text" placeholder="#f97316" maxlength="7"
                                   class="flex-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-2 py-2 text-xs font-mono focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        @error('color') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Icon name</label>
                        <input wire:model="icon" type="text" placeholder="e.g. burger"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="save" class="btn-primary flex-1 py-2.5 text-sm font-semibold">
                    {{ $editingId ? 'Save Changes' : 'Create' }}
                </button>
                <button wire:click="$set('showModal', false)" class="btn-secondary flex-1 py-2.5 text-sm font-semibold">
                    Cancel
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
