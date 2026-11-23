<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chatbot · DiaCare</title>
    <x-theme-script />
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="landing-page" data-authenticated="{{ auth()->check() ? 'true' : 'false' }}">
    <header class="landing-nav">
        <a href="{{ route('welcome') }}" class="landing-brand">
            <span class="brand-mark brand-logo">
                <svg viewBox="0 0 36 36"><path d="M18 3.5 29 8v8.5c0 7.2-4.7 12.9-11 16-6.3-3.1-11-8.8-11-16V8l11-4.5Z"/><path d="m11.5 18 4 4 9-9"/></svg>
            </span>
            <span>DiaCare</span>
        </a>
        <nav>
            <a href="{{ route('welcome') }}">Home</a>
            <a href="{{ route('welcome') }}#about">About</a>
            <a class="active" href="{{ route('assistant') }}">AI Chatbot</a>
            <a href="{{ route('knowledge') }}">Knowledge Sharing</a>
            <a href="{{ route('quiz') }}">Quiz</a>
            <x-theme-toggle />
            @auth
                <a class="landing-cta" href="{{ route('dashboard') }}">Dashboard</a>
            @else
                <a class="landing-link" href="{{ route('login') }}">Login</a>
                <a class="landing-cta" href="{{ route('register') }}">Sign up</a>
            @endauth
        </nav>
    </header>

    <main class="public-content public-chat" x-data="diaCareAssistant()" x-init="init()">
        <section class="public-hero">
            <span class="landing-kicker">Ask DiaCare</span>
            <h1>Helpful answers,<br><em>without the waiting.</em></h1>
            <p>
                @auth
                    Your conversation is saved securely to your DiaCare profile, so you can return to it while signed in.
                @else
                    You can try the chatbot without creating an account. Your guest conversation stays in this browser and is not saved to a DiaCare profile.
                @endauth
            </p>
        </section>

        <section class="assistant-full card">
            <div class="assistant-full-header">
                <div class="assistant-title">
                    <span class="assistant-avatar">✦</span>
                    <div><strong>DiaCare assistant</strong><span>Plain-language diabetes education</span></div>
                </div>
                <button type="button" class="secondary-button" @click.prevent="clearHistory()" :disabled="sending">Clear history</button>
            </div>
            <div class="assistant-full-messages" x-ref="assistantMessages">
                <template x-if="!messages.length">
                    <div class="assistant-welcome">
                        <strong>What would you like to learn?</strong>
                        <p>Ask about glucose, food, activity, medication, or keeping a useful record.</p>
                        <div class="assistant-prompts">
                            <button type="button" @click="draft='What can affect glucose readings?';sendMessage()">What can affect glucose readings?</button>
                            <button type="button" @click="draft='How can I make my glucose log useful?';sendMessage()">How can I make my glucose log useful?</button>
                        </div>
                    </div>
                </template>
                <template x-for="message in messages" :key="message.id">
                    <div class="assistant-message" :class="message.role === 'user' ? 'user' : 'assistant'"><span x-text="message.content"></span></div>
                </template>
                <div x-show="sending" class="assistant-message assistant"><span>Thinking…</span></div>
            </div>
            <form class="assistant-full-form" @submit.prevent="sendMessage()">
                <input type="text" x-model="draft" maxlength="2000" placeholder="Ask DiaCare a question…" autocomplete="off">
                <button type="submit" class="primary-button" :disabled="sending || !draft.trim()" x-text="sending ? 'Sending…' : 'Send'"></button>
            </form>
            <p class="assistant-disclaimer">For education and tracking support only. It does not replace professional medical advice.</p>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="landing-brand">
            <span class="brand-mark brand-logo">
                <svg viewBox="0 0 36 36"><path d="M18 3.5 29 8v8.5c0 7.2-4.7 12.9-11 16-6.3-3.1-11-8.8-11-16V8l11-4.5Z"/><path d="m11.5 18 4 4 9-9"/></svg>
            </span>
            <span>DiaCare</span>
        </div>
        <span>© {{ date('Y') }} DiaCare</span>
    </footer>
</body>
</html>
