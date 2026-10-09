@props(['route', 'active' => false])

<a href="{{ route($route) }}"
   {{ $attributes->merge([
       'class' => 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors ' .
           ($active
               ? 'bg-amber-50 text-amber-700 dark:bg-amber-900/20 dark:text-amber-400'
               : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-100')
   ]) }}>
    <span class="{{ $active ? 'text-amber-500' : 'text-gray-400 dark:text-gray-500' }}">
        {{ $icon }}
    </span>
    {{ $slot }}
</a>
