<x-app-layout>
    <x-slot name="title">{{ isset($productId) ? 'Edit Product' : 'New Product' }}</x-slot>

    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('products.index') }}"
           class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                {{ isset($productId) ? 'Edit Product' : 'New Product' }}
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                {{ isset($productId) ? 'Update product details, variants, add-ons and image.' : 'Fill in the details to add a new product.' }}
            </p>
        </div>
    </div>

    <livewire:products.product-form :productId="$productId ?? null" />

</x-app-layout>
