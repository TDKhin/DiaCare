<x-app-layout>
    <div class="page-container" x-data="{ range: '7 days' }">
        <div class="page-heading">
            <div><p class="eyebrow">Track</p><h1>Glucose</h1><p>Track and understand your blood sugar over time.</p></div>
            <button type="button" class="primary-button" @click="document.getElementById('reading-form').classList.toggle('hidden')"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>Add reading</button>
        </div>

        <div id="reading-form" class="card content-card hidden" style="margin-bottom: 17px;">
            <form method="POST" action="{{ route('glucose.store') }}">
                @csrf
                <div class="list-header"><div><div class="card-title">Add a glucose reading</div><div class="card-subtitle" style="margin-top: 4px;">Record a reading to keep your personal trend current.</div></div><button type="button" class="icon-button" @click="$el.closest('#reading-form').classList.add('hidden')" aria-label="Close form">&times;</button></div>
                <div class="profile-form" style="margin-top: 16px;"><div class="form-field"><label for="new-reading">Reading (mg/dL)</label><input id="new-reading" name="value" type="number" min="20" max="600" required placeholder="e.g. 120"></div><div class="form-field"><label for="reading-context">When was this?</label><select id="reading-context" name="context"><option>Before breakfast</option><option>After breakfast</option><option>Before lunch</option><option>After lunch</option><option>Before dinner</option><option>After dinner</option></select></div><div class="form-actions"><button type="submit" class="primary-button">Save reading</button></div></div>
            </form>
        </div>

        <div class="glucose-overview">
            <section class="card reading-card"><div class="reading-label">Latest reading</div>@if($readings)<div class="reading-number">{{ $readings[0]['value'] }} <span>mg/dL</span></div><div class="reading-context">{{ $readings[0]['tag'] === 'In range' ? 'Within your target range' : 'Outside your target range' }}</div><div class="card-subtitle" style="margin-top: 9px;">{{ $readings[0]['date'] }} · {{ $readings[0]['period'] }}</div>@else<div class="empty-state" style="min-height:105px;"><h3>No reading yet</h3><p>Add your first glucose reading to begin.</p></div>@endif</section>
            <section class="card reading-card"><div class="reading-label">Your target range</div><div class="target-range">80–130 <span>mg/dL</span></div><div class="range-track"><span class="range-marker"></span></div><div class="flex justify-between" style="margin-top: 8px;"><span class="card-subtitle">Below</span><span class="card-subtitle">In range</span><span class="card-subtitle">Above</span></div></section>
        </div>

                    <section class="card chart-card" style="margin-top: 17px;">

            <div class="chart-card-header"><div><div class="card-title">Glucose trend</div><div class="card-subtitle" style="margin-top: 4px;">Your readings from the selected period.</div></div><div class="chart-legend"><span class="legend-dot"></span>Reading</div></div>
            <div class="page-toolbar" style="margin: 18px 0 0;"><div class="filter-tabs"><button class="filter-tab" :class="{ active: range === '7 days' }" @click="range = '7 days'">7 days</button><button class="filter-tab" :class="{ active: range === '30 days' }" @click="range = '30 days'">30 days</button><button class="filter-tab" :class="{ active: range === '90 days' }" @click="range = '90 days'">90 days</button><button class="filter-tab" :class="{ active: range === 'All' }" @click="range = 'All'">All</button></div><span class="card-subtitle" x-text="'Showing ' + range"></span></div>
            @if($readings)
            <div class="chart-wrap">
                <svg viewBox="0 0 960 220" preserveAspectRatio="none" role="img" aria-label="Glucose trend line chart"><g stroke="#edf2f7" stroke-width="1"><line x1="0" y1="30" x2="960" y2="30"/><line x1="0" y1="82" x2="960" y2="82"/><line x1="0" y1="134" x2="960" y2="134"/><line x1="0" y1="186" x2="960" y2="186"/></g><path d="M0 121 C33 101 36 165 69 158 S110 73 145 94 S180 132 218 129 S252 177 287 172 S318 146 353 96 S400 73 429 103 S464 152 502 122 S540 109 570 88 S607 127 639 107 S684 68 717 92 S749 79 780 65 S821 105 851 118 S892 180 920 193 S945 99 960 111" fill="none" stroke="#367fe1" stroke-width="3.2" stroke-linecap="round"/><path d="M0 121 C33 101 36 165 69 158 S110 73 145 94 S180 132 218 129 S252 177 287 172 S318 146 353 96 S400 73 429 103 S464 152 502 122 S540 109 570 88 S607 127 639 107 S684 68 717 92 S749 79 780 65 S821 105 851 118 S892 180 920 193 S945 99 960 111 L960 220 L0 220Z" fill="url(#glucoseArea)" opacity=".8"/><g fill="#fff" stroke="#367fe1" stroke-width="2"><circle cx="0" cy="121" r="4"/><circle cx="145" cy="94" r="4"/><circle cx="287" cy="172" r="4"/><circle cx="429" cy="103" r="4"/><circle cx="570" cy="88" r="4"/><circle cx="717" cy="92" r="4"/><circle cx="851" cy="118" r="4"/><circle cx="920" cy="193" r="4"/><circle cx="960" cy="111" r="4"/></g></svg>
                <div class="chart-labels"><span>{{ $readings[count($readings) - 1]['date'] ?? '' }}</span><span>{{ $readings[0]['date'] ?? '' }}</span></div>
            </div>
            @else
            <div class="empty-state" style="min-height:250px;"><span class="empty-state-icon"><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17 9 12l4 3 7-8"/><path d="M15 7h5v5"/></svg></span><h3>No glucose trend yet</h3><p>Your personal chart will appear after you add readings.</p></div>
            @endif
        </section>

        <section class="card content-card" style="margin-top: 17px;">
            <div class="list-header"><div><div class="card-title">Recent readings</div><div class="card-subtitle" style="margin-top: 4px;">Your latest logged readings.</div></div><span class="card-subtitle">{{ count($readings) }} readings</span></div>
            <div class="table-list"><div class="table-row header"><span>Context</span><span>Reading</span><span>Status</span><span>Actions</span></div>
                @forelse ($readings as $reading)
                    <div class="table-row"><span><span class="table-primary block">{{ $reading['period'] }}</span><span class="table-secondary block">{{ $reading['date'] }}</span></span><span class="table-value {{ $reading['tag'] === 'In range' ? 'good' : ($reading['tag'] === 'Low' ? 'text-blue-500' : 'high') }}">{{ $reading['value'] }} mg/dL</span><span><span class="status-badge {{ $reading['tag'] === 'In range' ? 'green' : 'orange' }}">{{ $reading['tag'] }}</span></span><span class="table-actions"><button class="icon-button" aria-label="Edit reading"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/></svg></button><button class="icon-button" aria-label="Delete reading"><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg></button></span></div>
                @empty
                    <div class="empty-state" style="grid-column:1 / -1; min-height:150px;"><h3>No readings logged yet</h3><p>Use “Add reading” above to start your personal record.</p></div>
                @endforelse
            </div>
        </section>
    </div>
    <button type="button" class="chat-fab" @click="$dispatch('open-assistant')" aria-label="Open DiaCare assistant"><span class="chat-dot"></span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.8 8.8 0 0 1-3.3-.63L4 20l1.64-3.62A7.4 7.4 0 0 1 4.5 12 7.5 7.5 0 0 1 12 4.5h.5A7.5 7.5 0 0 1 20 11.5Z"/><path d="M9 12h.01M12 12h.01M15 12h.01"/></svg></button>
</x-app-layout>
