<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }} — Login</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Dynamic theme CSS variables --}}
    <style>:root { {!! \App\Services\ThemeService::getCssVars() !!} }</style>
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="mb-8 flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-xl bg-primary-500 flex items-center justify-center shadow-lg">
                <svg class="w-7 h-7" style="color: rgb(var(--color-primary-contrast))" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v1a1 1 0 01-.293.707L13 10.414V16a1 1 0 01-.553.894l-4 2A1 1 0 017 18v-7.586L3.293 5.707A1 1 0 013 5V4z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-800">ZAC Burger Club</h1>
            <p class="text-sm text-gray-500">Point of Sale System</p>
        </div>

        <div class="w-full max-w-sm bg-white rounded-2xl shadow-sm border border-gray-200 px-6 py-7">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
