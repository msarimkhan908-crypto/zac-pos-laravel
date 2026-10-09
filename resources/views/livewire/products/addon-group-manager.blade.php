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

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white">Add-on Groups</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Reusable modifier groups shared across products</p>
        </div>
        <button wire:click="openCreate" class="btn-primary px-4 py-2 text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Group
        </button>
    </div>

    @if($groups->isEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h8m-8 4h6"/>
        </svg>
        <p class="text-sm text-gray-500 dark:text-gray-400">No add-on groups yet. Create one to attach to products.</p>
    </div>
    @else
    <div class="space-y-3">
        @foreach($groups as $group)
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <p class="font-semibold text-gray-800 dark:text-white text-sm">{{ $group->name }}</p>
                        @if($group->is_required)
                        <span class="text-xs bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-2 py-0.5 rounded-full font-medium">Required</span>
                        @endif
                        <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 px-2 py-0.5 rounded-full">
                            Pick {{ $group->min_selectable }}–{{ $group->max_selectable }}
                        </span>
                        <span class="text-xs text-gray-400 dark:text-gray-500">
                            · used by {{ $group->products_count }} product{{ $group->products_count !== 1 ? 's' : '' }}
                        </span>
                    </div>
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @foreach($group->options as $opt)
                        <span class="text-xs bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-300 px-2.5 py-1 rounded-full">
                            {{ $opt->name }}
                            @if($opt->extra_price > 0)
                            <span class="text-primary-500">+{{ currency($opt->extra_price) }}</span>
                            @endif
                        </span>
                        @endforeach
                        @if($group->options->isEmpty())
                        <span class="text-xs text-gray-400 italic">No options yet</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-1 flex-shrink-0">
                    <button wire:click="openEdit('{{ $group->id }}')"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                    <button wire:click="delete('{{ $group->id }}')"
                            wire:confirm="Delete '{{ $group->name }}'? All options inside it will also be deleted."
                            class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Modal --}}
    @if($showModal)
    <div class="fixed inset-0 z-40 flex items-center justify-center p-4"
         @keydown.escape.window="$wire.set('showModal', false)">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto p-6 z-50">
            <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-5">
                {{ $editingId ? 'Edit Group' : 'New Add-on Group' }}
            </h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Group name <span class="text-red-500">*</span></label>
                    <input wire:model="name" type="text" placeholder="e.g. Extras, Remove Items"
                           class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Min select</label>
                        <input wire:model="minSelectable" type="number" min="0" max="10"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Max select</label>
                        <input wire:model="maxSelectable" type="number" min="1" max="10"
                               class="w-full border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="flex flex-col justify-end">
                        <label class="flex items-center gap-2 cursor-pointer pb-2">
                            <input wire:model="isRequired" type="checkbox"
                                   class="w-4 h-4 rounded border-gray-300 dark:border-gray-600 text-primary-500 focus:ring-primary-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Required</span>
                        </label>
                    </div>
                </div>

                {{-- Options list --}}
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs font-medium text-gray-600 dark:text-gray-400">Options</label>
                        <button type="button" wire:click="addOption"
                                class="text-xs text-primary-600 dark:text-primary-400 hover:underline font-medium">+ Add option</button>
                    </div>

                    @if(empty($options))
                    <p class="text-xs text-gray-400 dark:text-gray-500 py-2">No options yet. Add at least one.</p>
                    @else
                    <div class="space-y-2">
                        @foreach($options as $i => $opt)
                        <div class="flex items-center gap-2">
                            <input wire:model="options.{{ $i }}.name" type="text" placeholder="Option name"
                                   class="flex-1 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <div class="flex items-center border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-lg overflow-hidden focus-within:ring-2 focus-within:ring-primary-500 w-28">
                                <span class="px-2 text-xs text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-600 py-2">+</span>
                                <input wire:model="options.{{ $i }}.extra_price" type="number" step="0.01" min="0" placeholder="0.00"
                                       class="w-full px-2 py-2 text-xs bg-transparent dark:text-gray-200 focus:outline-none">
                            </div>
                            <button type="button" wire:click="removeOption({{ $i }})"
                                    class="p-1.5 rounded text-gray-400 hover:text-red-500 transition-colors flex-shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                        @error("options.$i.name") <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        @endforeach
                    </div>
                    @endif
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
