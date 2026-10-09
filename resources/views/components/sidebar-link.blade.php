{{-- Legacy component kept for compatibility; layout now uses sidebar-nav-link CSS class directly. --}}
@props(['route', 'active' => false])

<a href="{{ route($route) }}"
   class="sidebar-nav-link {{ $active ? 'active' : '' }}"
   {{ $attributes }}>
    @isset($icon)
        <span class="nav-icon w-5 h-5">{{ $icon }}</span>
    @endisset
    {{ $slot }}
</a>
