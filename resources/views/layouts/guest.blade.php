<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'DiaCare' }}</title>

        <x-theme-script />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 dark:bg-gray-900 sm:justify-center sm:pt-0">
            <div class="absolute right-4 top-4">
                <x-theme-toggle />
            </div>

            <div>
                <a href="{{ url('/') }}" class="flex flex-col items-center gap-2">
                    <x-application-logo class="h-20 w-20 fill-current text-gray-500" />
                    <span class="text-xl font-semibold text-gray-800 dark:text-gray-100">DiaCare</span>
                </a>
            </div>

            <div class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md dark:bg-gray-800 sm:max-w-md sm:rounded-lg">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
