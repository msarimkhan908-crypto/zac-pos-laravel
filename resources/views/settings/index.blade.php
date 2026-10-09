<x-app-layout>
    <x-slot name="title">Settings</x-slot>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Settings</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure your restaurant and POS preferences</p>
    </div>

    {{-- Tabs --}}
    @php $tab = request('tab', 'general') @endphp
    <div class="flex gap-1 mb-6 border-b border-gray-200 dark:border-gray-700">
        @foreach(['general' => 'General', 'appearance' => 'Appearance'] as $slug => $label)
        <a href="{{ route('settings.index', ['tab' => $slug]) }}"
           class="px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors
                  {{ $tab === $slug
                      ? 'border-primary-500 text-primary-600 dark:text-primary-400'
                      : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:border-gray-300' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- General tab --}}
    @if($tab === 'general')
    <x-coming-soon title="General Settings" description="Restaurant name, currency, tax, and delivery charge settings will appear here." />
    @endif

    {{-- Appearance tab --}}
    @if($tab === 'appearance')
    <livewire:settings.appearance />
    @endif

</x-app-layout>
