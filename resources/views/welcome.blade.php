<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>DiaCare</title>

        <x-theme-script />

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
        <header class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
            <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ url('/') }}" class="text-xl font-semibold tracking-tight">DiaCare</a>

                <nav class="flex items-center gap-2 sm:gap-3">
                    <x-theme-toggle />

                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md border border-gray-300 px-4 py-1.5 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                            {{ __('Dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-md px-4 py-1.5 text-sm font-medium hover:bg-gray-50 dark:hover:bg-gray-800">
                            {{ __('Log in') }}
                        </a>
                        <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-indigo-500">
                            {{ __('Register') }}
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">Diabetes care, simplified</p>
                <h1 class="mt-3 text-4xl font-semibold tracking-tight sm:text-5xl">Welcome to DiaCare</h1>
                <p class="mt-4 text-lg text-gray-600 dark:text-gray-300">
                    Create an account or sign in to open your dashboard. Health tracking features will be added in a later step.
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            {{ __('Go to dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            {{ __('Get started') }}
                        </a>
                        <a href="{{ route('login') }}" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </div>
            </div>
        </main>
    </body>
</html>
