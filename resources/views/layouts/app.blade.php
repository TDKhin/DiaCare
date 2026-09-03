<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'DiaCare' }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div x-data="{ sidebarOpen: false }" class="min-h-screen md:flex">
            @include('layouts.navigation')

            <div class="dashboard-main min-w-0 flex-1">
                <div class="mobile-topbar">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 no-underline">
                        <span class="brand-mark" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.35-7-10.1A4.1 4.1 0 0 1 9.1 7c1.3 0 2.35.65 2.9 1.53A3.4 3.4 0 0 1 14.9 7 4.1 4.1 0 0 1 19 10.9C19 16.65 12 21 12 21Z"/><path d="M12 9v5M9.5 11.5h5"/></svg>
                        </span>
                        <span class="brand-name">Diabetes Care</span>
                    </a>
                    <button type="button" class="mobile-menu-button" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle navigation">
                        <svg x-show="!sidebarOpen" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="sidebarOpen" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                </div>

                <main>
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
