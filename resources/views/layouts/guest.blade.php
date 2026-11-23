<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $title ?? 'DiaCare' }}</title>
        <x-theme-script />
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="guest-shell">
            <a href="{{ url('/') }}" class="guest-brand no-underline">
                <span class="brand-mark" aria-hidden="true"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.35-7-10.1A4.1 4.1 0 0 1 9.1 7c1.3 0 2.35.65 2.9 1.53A3.4 3.4 0 0 1 14.9 7 4.1 4.1 0 0 1 19 10.9C19 16.65 12 21 12 21Z"/><path d="M12 9v5M9.5 11.5h5"/></svg></span>
                <span><span class="brand-name block">Diabetes Care</span><span class="brand-subtitle block">Wellness companion</span></span>
            </a>
            <section class="guest-card">
                <div class="guest-card-top"><span class="guest-overline">Your care, simplified</span><span class="guest-actions"><x-theme-toggle /><span class="guest-status-dot"></span></span></div>
                {{ $slot }}
            </section>
            <p class="guest-footer">A calm place to track your everyday wellness routine.</p>
        </main>
    </body>
</html>
