@php
    $initials = collect(explode(' ', trim(Auth::user()->name)))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('');
    $navGroups = [
        'Track' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'dashboard'],
            ['label' => 'Glucose', 'route' => 'glucose', 'icon' => 'glucose'],
            ['label' => 'Meals', 'route' => 'meals', 'icon' => 'meals'],
            ['label' => 'Activity', 'route' => 'activity', 'icon' => 'activity'],
            ['label' => 'Profile', 'route' => 'profile.edit', 'icon' => 'profile'],
        ],
        'More' => [
            ['label' => 'Meal Photo AI', 'route' => 'meal-photo', 'icon' => 'scan'],
            ['label' => 'Medications', 'route' => 'medications', 'icon' => 'medications'],
            ['label' => 'Reports', 'route' => 'reports', 'icon' => 'reports'],
            ['label' => 'Alerts', 'route' => 'alerts', 'icon' => 'alerts'],
        ],
    ];
@endphp

<aside class="dashboard-sidebar" :class="{ 'is-open': sidebarOpen }" @click.outside="sidebarOpen = false">
    <a href="{{ route('dashboard') }}" class="brand-lockup no-underline">
        <span class="brand-mark" aria-hidden="true">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.35-7-10.1A4.1 4.1 0 0 1 9.1 7c1.3 0 2.35.65 2.9 1.53A3.4 3.4 0 0 1 14.9 7 4.1 4.1 0 0 1 19 10.9C19 16.65 12 21 12 21Z"/><path d="M12 9v5M9.5 11.5h5"/></svg>
        </span>
        <span><span class="brand-name block">Diabetes Care</span><span class="brand-subtitle block">Wellness companion</span></span>
    </a>

    @foreach ($navGroups as $group => $links)
        <div class="sidebar-section-label">{{ $group }}</div>
        <nav class="sidebar-nav" aria-label="{{ $group }} navigation">
            @foreach ($links as $link)
                <a href="{{ route($link['route']) }}" class="sidebar-link {{ request()->routeIs($link['route']) ? 'active' : '' }}" @click="sidebarOpen = false">
                    <span class="sidebar-icon">
                        @switch($link['icon'])
                            @case('dashboard')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                                @break
                            @case('glucose')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3S6 9.2 6 14a6 6 0 0 0 12 0c0-4.8-6-11-6-11Z"/><path d="M9.5 15.5a2.7 2.7 0 0 0 2.5 1.8"/></svg>
                                @break
                            @case('meals')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 3v8a3 3 0 0 0 6 0V3M7 3v18M4 8h6M15 3v18M15 3c3 0 5 2 5 5v3h-5"/></svg>
                                @break
                            @case('activity')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12h4l2-7 4 14 2-7h6"/></svg>
                                @break
                            @case('profile')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3"/><path d="M5 20a7 7 0 0 1 14 0"/></svg>
                                @break
                            @case('scan')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7V5a1 1 0 0 1 1-1h2M17 4h2a1 1 0 0 1 1 1v2M20 17v2a1 1 0 0 1-1 1h-2M7 20H5a1 1 0 0 1-1-1v-2"/><path d="M8 12h8M12 8v8"/></svg>
                                @break
                            @case('medications')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 3v4h6V3M9 13h6M9 17h4"/></svg>
                                @break
                            @case('reports')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19V5a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v14"/><path d="M8 16v-3M12 16V8M16 16v-5"/><path d="M3 19h18"/></svg>
                                @break
                            @case('alerts')
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
                                @break
                        @endswitch
                    </span>
                    <span>{{ $link['label'] }}</span>
                </a>
            @endforeach
        </nav>
    @endforeach

    <div class="sidebar-account">
        <span class="avatar">{{ $initials ?: 'DC' }}</span>
        <span class="min-w-0 flex-1">
            <span class="account-name block">{{ Auth::user()->name }}</span>
            <span class="account-email block">{{ Auth::user()->email }}</span>
        </span>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="logout-link w-full border-0 bg-transparent text-left">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M21 19V5a2 2 0 0 0-2-2h-6"/></svg>
            <span>Log out</span>
        </button>
    </form>
</aside>
