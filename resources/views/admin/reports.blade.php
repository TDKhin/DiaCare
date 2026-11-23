<x-app-layout>
    <div class="page-container">
        <div class="page-heading"><div><p class="eyebrow">Administration</p><h1>Report management</h1><p>Review and maintain health records by account.</p></div></div>
        @if(session('status'))<div class="alert-success">{{ session('status') }}</div>@endif
        @if($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif
        <div class="summary-grid">
            <section class="card summary-card"><div class="summary-label">Users</div><div class="summary-value">{{ $userCount }}</div></section>
            <section class="card summary-card"><div class="summary-label">Glucose records</div><div class="summary-value">{{ $readingCount }}</div></section>
            <section class="card summary-card"><div class="summary-label">Meal analyses</div><div class="summary-value">{{ $mealCount }}</div></section>
            <section class="card summary-card"><div class="summary-label">Active medications</div><div class="summary-value">{{ $medicationCount }}</div></section>
        </div>
        <section class="card content-card" style="margin-top:17px;"><div class="list-header"><div><div class="card-title">Glucose records</div><div class="card-subtitle" style="margin-top:4px;">Admin can correct or delete manually recorded readings.</div></div></div>
            <div class="table-list glucose-admin-list"><div class="table-row header"><span>User</span><span>Value</span><span>Context</span><span>Recorded</span><span>Actions</span></div>
                @forelse($readings as $reading)
                    <div class="table-row"><span class="table-primary">{{ optional($reading->user)->name ?? 'Unknown user' }}</span><form method="POST" action="{{ route('admin.glucose.update', $reading) }}" class="admin-inline-form">@csrf @method('PATCH')<input name="value" type="number" min="1" max="1000" step="0.1" value="{{ $reading->value }}" required><input name="context" value="{{ $reading->context }}" required><input name="recorded_at" type="datetime-local" value="{{ optional($reading->recorded_at)->format('Y-m-d\\TH:i') }}" required><input name="notes" value="{{ $reading->notes }}" placeholder="Notes"><button class="secondary-button" type="submit">Update</button></form><form method="POST" action="{{ route('admin.glucose.delete', $reading) }}" onsubmit="return confirm('Delete this glucose reading?')">@csrf @method('DELETE')<button class="danger-button" type="submit">Delete</button></form></div>
                @empty<div class="empty-state" style="grid-column:1/-1;min-height:120px;"><h3>No manual glucose readings</h3></div>@endforelse
            </div>
        </section>
        <section class="card content-card" style="margin-top:17px;"><div class="list-header"><div><div class="card-title">Account coverage</div><div class="card-subtitle" style="margin-top:4px;">Glucose includes manual readings and saved meal glucose estimates.</div></div><span class="card-subtitle">{{ $activityCount }} activity logs</span></div>
            <div class="table-list five-columns"><div class="table-row header"><span>User</span><span>Glucose</span><span>Meals</span><span>Exercise</span><span>Medication</span></div>
                @foreach($recentUsers as $user)<div class="table-row"><span class="table-primary">{{ $user->name }}</span><span class="table-secondary">{{ $user->glucose_count }}</span><span class="table-secondary">{{ $user->meal_photos_count }}</span><span class="table-secondary">{{ $user->activity_logs_count }}</span><span class="table-secondary">{{ $user->medications_count }}</span></div>@endforeach
            </div>
        </section>
    </div>
</x-app-layout>
