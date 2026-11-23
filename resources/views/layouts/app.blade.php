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
    <body data-authenticated="true">
        <div x-data="diaCareAssistant()" x-init="init()" class="min-h-screen md:flex">
            @include('layouts.navigation')

            <div class="dashboard-main min-w-0 flex-1">
                <div class="mobile-topbar">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2 no-underline">
                        <span class="brand-mark" aria-hidden="true">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.35-7-10.1A4.1 4.1 0 0 1 9.1 7c1.3 0 2.35.65 2.9 1.53A3.4 3.4 0 0 1 14.9 7 4.1 4.1 0 0 1 19 10.9C19 16.65 12 21 12 21Z"/><path d="M12 9v5M9.5 11.5h5"/></svg>
                        </span>
                        <span class="brand-name">Diabetes Care</span>
                    </a>
                    <div class="mobile-topbar-actions"><x-theme-toggle /><button type="button" class="mobile-menu-button" @click="sidebarOpen = !sidebarOpen" aria-label="Toggle navigation">
                        <svg x-show="!sidebarOpen" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="sidebarOpen" x-cloak width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button></div>
                </div>

                <main>
                    {{ $slot }}
                </main>
            </div>

            <div x-show="assistantOpen" x-cloak class="assistant-backdrop" @click="assistantOpen = false"></div>
            <aside x-show="assistantOpen" x-cloak x-transition class="assistant-panel" aria-label="DiaCare assistant" @keydown.escape.window="assistantOpen = false">
                <header class="assistant-header">
                    <div class="assistant-title"><span class="assistant-avatar">✦</span><div><strong>DiaCare Assistant</strong><span>Wellness guidance</span></div></div>
                    <div class="flex items-center gap-2">
                        <button type="button" class="secondary-button !px-3 !py-1.5 !text-xs" @click.prevent="clearHistory()" :disabled="sending">Clear history</button>
                        <button type="button" class="icon-button" @click="assistantOpen = false" aria-label="Close assistant">×</button>
                    </div>
                </header>
                <div class="assistant-messages" x-ref="assistantMessages">
                    <template x-if="messages.length === 0"><div class="assistant-welcome"><span class="assistant-welcome-icon">✦</span><strong>How can I help today?</strong><p>Ask about glucose patterns, food tracking, movement, or preparing for a clinician visit.</p><div class="assistant-prompts"><button type="button" @click="draft = 'What should I track before my next diabetes appointment?'">Prepare for an appointment</button><button type="button" @click="draft = 'How can I make my glucose log more useful?'">Improve my glucose log</button><button type="button" @click="draft = 'What are signs that need urgent medical help?'">Learn urgent warning signs</button></div></div></template>
                    <template x-for="message in messages" :key="message.id || message.created_at + message.content"><div class="assistant-message" :class="message.role === 'user' ? 'user' : 'assistant'"><span x-text="message.content"></span></div></template>
                    <div x-show="sending" class="assistant-message assistant"><span class="assistant-typing">Thinking…</span></div>
                </div>
                <form class="assistant-form" @submit.prevent="sendMessage">
                    <input x-model="draft" type="text" maxlength="2000" placeholder="Ask DiaCare Assistant…" aria-label="Message assistant">
                    <button type="submit" class="primary-button" :disabled="sending || !draft.trim()" aria-label="Send message"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 4 16 8-16 8 3-8-3-8Z"/><path d="M7 12h13"/></svg></button>
                </form>
                <p class="assistant-disclaimer">Educational wellness information only. Verify readings on your device and contact a clinician for personal decisions or urgent symptoms.</p>
            </aside>
        </div>
    </body>
</html>
