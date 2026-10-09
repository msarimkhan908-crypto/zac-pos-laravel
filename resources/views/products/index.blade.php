<x-app-layout>
    <x-slot name="title">Products</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Products</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Manage your menu items, categories and modifiers</p>
    </div>

    {{-- Tabs --}}
    @php $tab = request('tab', 'products') @endphp
    <div class="flex gap-1 mb-6 border-b border-gray-200 dark:border-gray-700">
        @foreach(['products' => 'Products', 'categories' => 'Categories', 'addons' => 'Add-ons'] as $slug => $label)
        <a href="{{ route('products.index', ['tab' => $slug]) }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors
                  {{ $tab === $slug
                      ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                      : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    @if($tab === 'categories')
        <livewire:products.category-list />
    @elseif($tab === 'addons')
        <livewire:products.addon-group-manager />
    @else
        <livewire:products.product-list />
    @endif

</x-app-layout>
