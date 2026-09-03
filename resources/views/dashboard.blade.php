@php($firstName = explode(' ', trim(Auth::user()->name))[0] ?? Auth::user()->name)

<x-app-layout>
    <div class="page-container">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Your health overview</p>
                <h1>Hello, {{ $firstName ?: Auth::user()->name }} <span aria-hidden="true">&#128075;</span></h1>
                <p>Here is your wellness snapshot for today. Small, consistent steps make a difference.</p>
            </div>
            <a href="{{ route('profile.edit') }}" class="primary-button">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg>
                View profile
            </a>
        </div>

        <div class="summary-grid">
            <div class="card summary-card">
                <div class="summary-card-top"><span class="summary-label">Latest glucose</span><span class="summary-icon blue"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 3S6 9.2 6 14a6 6 0 0 0 12 0c0-4.8-6-11-6-11Z"/></svg></span></div>
                <div class="summary-value">{{ $latestReading ?? '—' }} @if($latestReading)<small>mg/dL</small>@endif</div>
                <div class="summary-meta {{ $latestReading ? 'warning' : '' }}">{{ $latestReading ? 'Log another reading to track your trend' : 'No readings logged yet' }}</div>
            </div>
            <div class="card summary-card">
                <div class="summary-card-top"><span class="summary-label">7-day average</span><span class="summary-icon green"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m4 14 5-5 4 4 7-7"/><path d="M15 6h5v5"/></svg></span></div>
                <div class="summary-value">{{ $averageReading ?? '—' }} @if($averageReading)<small>mg/dL</small>@endif</div>
                <div class="summary-meta {{ $averageReading ? 'good' : '' }}">{{ $averageReading ? 'Based on your last 7 days' : 'Add readings to calculate' }}</div>
            </div>
            <div class="card summary-card">
                <div class="summary-card-top"><span class="summary-label">BMI</span><span class="summary-icon purple"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 20V10M12 20V4M19 20v-7"/></svg></span></div>
                <div class="summary-value">—</div>
                <div class="summary-meta">Set up your profile to track</div>
            </div>
            <div class="card summary-card">
                <div class="summary-card-top"><span class="summary-label">Today’s activity</span><span class="summary-icon orange"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M3 12h4l2-7 4 14 2-7h6"/></svg></span></div>
                <div class="summary-value">{{ $activityMinutes }} <small>min</small></div>
                <div class="summary-meta">0% of your weekly goal</div>
            </div>
        </div>

        <div class="section-grid" style="margin-top: 17px;">
            <section class="card quick-card">
                <div class="list-header"><div><div class="card-title">Quick actions</div><div class="card-subtitle" style="margin-top: 4px;">Keep your care routine up to date.</div></div></div>
                <div class="quick-actions">
                    <a class="quick-action" href="{{ route('glucose') }}"><span class="quick-action-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3S6 9.2 6 14a6 6 0 0 0 12 0c0-4.8-6-11-6-11Z"/></svg></span><span>Add glucose</span></a>
                    <a class="quick-action" href="{{ route('meals') }}"><span class="quick-action-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 3v8a3 3 0 0 0 6 0V3M7 3v18M4 8h6M15 3v18"/></svg></span><span>Log meal</span></a>
                    <a class="quick-action" href="{{ route('medications') }}"><span class="quick-action-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 13h6"/></svg></span><span>Add medication</span></a>
                    <a class="quick-action" href="{{ route('activity') }}"><span class="quick-action-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M3 12h4l2-7 4 14 2-7h6"/></svg></span><span>Log exercise</span></a>
                    <a class="quick-action" href="{{ route('meal-photo') }}"><span class="quick-action-icon"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M8 12h8M12 8v8"/></svg></span><span>Analyze meal</span></a>
                </div>
            </section>
            <section class="card medication-card">
                <div class="list-header"><div><div class="card-title">Today's medication</div><div class="card-subtitle" style="margin-top: 4px;">Stay on schedule with your care plan.</div></div><a href="{{ route('medications') }}" class="text-[10px] font-bold text-blue-500 no-underline">View all</a></div>
                <div class="medication-list">
                    @forelse ($medications as $medication)
                        <div class="medication-row"><span class="medication-pill"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 13h6"/></svg></span><span><span class="medication-name block">{{ $medication['name'] }}</span><span class="medication-dose block">{{ $medication['dose'] }}</span></span><span class="medication-time">{{ $medication['time'] }}</span></div>
                    @empty
                        <div class="empty-state" style="min-height:118px;"><span class="empty-state-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 13h6"/></svg></span><h3>No medications added</h3><p>Add your care-plan medications when ready.</p></div>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="section-grid">
            <section class="card chart-card">
                <div class="chart-card-header"><div><div class="card-title">Glucose trend</div><div class="card-subtitle" style="margin-top: 4px;">Your readings over the last 7 days.</div></div><a href="{{ route('glucose') }}" class="text-[10px] font-bold text-blue-500 no-underline">View details</a></div>
                @if ($glucosePoints->isEmpty())
                    <div class="empty-state" style="min-height:214px;"><span class="empty-state-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17 9 12l4 3 7-8"/><path d="M15 7h5v5"/></svg></span><h3>No glucose trend yet</h3><p>Log your first reading to see your personal chart.</p></div>
                @else
                <div class="chart-wrap">
                    <svg viewBox="0 0 760 190" preserveAspectRatio="none" role="img" aria-label="Glucose trend line chart">
                        <defs><linearGradient id="glucoseArea" x1="0" x2="0" y1="0" y2="1"><stop offset="0" stop-color="#3b82f6" stop-opacity=".14"/><stop offset="1" stop-color="#3b82f6" stop-opacity="0"/></linearGradient></defs>
                        <g stroke="#edf2f7" stroke-width="1"><line x1="0" y1="32" x2="760" y2="32"/><line x1="0" y1="78" x2="760" y2="78"/><line x1="0" y1="124" x2="760" y2="124"/><line x1="0" y1="170" x2="760" y2="170"/></g>
                        @php($areaPath = $chartPath.' L760 190 L0 190Z')
                        <path d="{{ $areaPath }}" fill="url(#glucoseArea)"/>
                        <path d="{{ $chartPath }}" fill="none" stroke="#367fe1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                        <g fill="#fff" stroke="#367fe1" stroke-width="2">@foreach($chartCoordinates as $point)<circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4"/>@endforeach</g>
                    </svg>
                    <div class="chart-labels"><span>{{ optional($glucosePoints->first()?->recorded_at)->format('M d') }}</span><span>{{ optional($glucosePoints->last()?->recorded_at)->format('M d') }}</span></div>
                </div>
                @endif
            </section>
            <section class="card content-card">
                <div class="list-header"><div><div class="card-title">Care reminders</div><div class="card-subtitle" style="margin-top: 4px;">A few things to keep in mind.</div></div><a href="{{ route('alerts') }}" class="text-[10px] font-bold text-blue-500 no-underline">See all</a></div>
                <div class="alert-list" style="margin-top: 15px;">
                    <div class="alert-item" style="padding: 11px;"><span class="alert-item-icon orange"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 3v10M12 17v1"/></svg></span><div><h3>Glucose check-in</h3><p>Keep logging readings around your meals.</p></div></div>
                    <div class="alert-item" style="padding: 11px;"><span class="alert-item-icon blue"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 13h6"/></svg></span><div><h3>Evening medication</h3><p>Next dose is scheduled for 8:00 PM.</p></div></div>
                </div>
            </section>
        </div>
    </div>

    <button type="button" class="chat-fab" @click="$dispatch('open-assistant')" aria-label="Open DiaCare assistant" x-data="{ open: false }" @click="open = !open" title="DiaCare assistant">
        <span class="chat-dot"></span>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.8 8.8 0 0 1-3.3-.63L4 20l1.64-3.62A7.4 7.4 0 0 1 4.5 12 7.5 7.5 0 0 1 12 4.5h.5A7.5 7.5 0 0 1 20 11.5Z"/><path d="M9 12h.01M12 12h.01M15 12h.01"/></svg>
    </button>
</x-app-layout>
