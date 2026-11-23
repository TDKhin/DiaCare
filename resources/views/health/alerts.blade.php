<x-app-layout>
    <div class="page-container alerts-page" x-data="diaCareAlertSound(@js($alerts->where('read', false)->pluck('key')->values()))" x-init="init()">
        <div class="page-heading">
            <div><p class="eyebrow">Your care center</p><h1>Alerts</h1><p>Important updates and reminders based on your recent health records.</p></div>
            <div class="alerts-summary"><span class="alerts-unread-dot"></span><strong>{{ $unreadCount }}</strong><span>unread</span></div>
        </div>

        @if (session('status') === 'alert-read')
            <div class="alert-success">Alert marked as read.</div>
        @endif

        <section class="alerts-toolbar card">
            <div><div class="card-title">Alert center</div><div class="card-subtitle" style="margin-top:4px;">Review recent updates and keep your care routine on track.</div></div>
            <button type="button" class="secondary-button" @click="toggle()" :aria-pressed="enabled.toString()" x-text="enabled ? 'Alert sound: On' : 'Enable alert sound'"></button>
            <nav class="alert-filters" aria-label="Alert filters">
                <a href="{{ route('alerts', ['filter' => 'all']) }}" class="{{ $filter === 'all' ? 'active' : '' }}">All <span>{{ $totalCount }}</span></a>
                <a href="{{ route('alerts', ['filter' => 'unread']) }}" class="{{ $filter === 'unread' ? 'active' : '' }}">Unread <span>{{ $unreadCount }}</span></a>
                <a href="{{ route('alerts', ['filter' => 'important']) }}" class="{{ $filter === 'important' ? 'active' : '' }}">Important</a>
            </nav>
        </section>

        <section class="alert-center-list">
            @forelse ($alerts as $alert)
                @php($type = $alert['type'] ?? 'information')
                <article class="alert-center-card {{ $alert['read'] ? 'is-read' : 'is-unread' }} alert-type-{{ $type }}">
                    <div class="alert-type-icon" aria-hidden="true">
                        @if ($type === 'critical')<svg viewBox="0 0 24 24"><path d="M12 3 2.8 20h18.4L12 3Z"/><path d="M12 9v5M12 17h.01"/></svg>
                        @elseif ($type === 'warning')<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 16h.01"/></svg>
                        @elseif ($type === 'reminder')<svg viewBox="0 0 24 24"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9ZM10 21h4"/></svg>
                        @else<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/></svg>@endif
                    </div>
                    <div class="alert-center-content">
                        <div class="alert-center-top"><span class="alert-type-label">{{ ucfirst($type) }}</span><time datetime="{{ $alert['date'] }}">{{ $alert['date'] }}</time></div>
                        <h2>{{ $alert['title'] }}</h2><p>{{ $alert['message'] }}</p>
                    </div>
                    <div class="alert-center-action">
                        @if ($alert['read'])<span class="read-state"><span>✓</span> Read</span>
                        @else
                            <span class="unread-state"><span></span> Unread</span>
                            <form method="POST" action="{{ route('alerts.read', $alert['key']) }}">@csrf<button type="submit" class="secondary-button">Mark as read</button></form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="card empty-state alerts-empty"><span class="empty-state-icon">✓</span><h3>No alerts in this view</h3><p>Try another filter or continue recording your health data.</p></div>
            @endforelse
        </section>
        <p class="alerts-disclaimer">DiaCare reminders support your routine and do not replace medical advice or emergency care.</p>
    </div>
    <button type="button" class="chat-fab" @click="$dispatch('open-assistant')" aria-label="Open DiaCare assistant"><span class="chat-dot"></span>✦</button>
</x-app-layout>
