<style>
    .activity-medication-sections { display: grid; grid-template-columns: 1fr; gap: 17px; }
    .activity-admin-table .table-row { column-gap: 10px; }
    .exercise-admin-table .table-row { grid-template-columns: minmax(130px, 1.15fr) minmax(150px, 1.25fr) minmax(90px, .8fr) minmax(95px, .8fr) minmax(130px, 1fr) 78px; }
    .medication-admin-table .table-row { grid-template-columns: minmax(130px, 1.05fr) minmax(135px, 1fr) minmax(100px, .8fr) minmax(170px, 1.35fr) minmax(135px, 1fr) minmax(85px, .7fr) 78px; }
    .activity-admin-table .table-row > span { min-width: 0; }
    .activity-admin-table .table-primary,
    .activity-admin-table .table-secondary { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .activity-admin-table .delete-button { border: 1px solid #2f80ed; border-radius: 8px; background: #2f80ed; color: #fff; box-shadow: 0 5px 10px rgba(47,128,237,.14); }
    .activity-admin-table .delete-button:hover { border-color: #1e64d6; background: #1e64d6; }
    .activity-admin-table .record-actions { display: flex; justify-content: flex-end; }
    @media (max-width: 1100px) {
        .activity-admin-table { overflow-x: auto; }
        .exercise-admin-table .table-row { min-width: 760px; }
        .medication-admin-table .table-row { min-width: 920px; }
    }
</style>
<x-app-layout>
    <div class="page-container">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Administration</p>
                <h1>Exercise &amp; medication</h1>
                <p>Review exercise and medication records submitted by users.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <div class="activity-medication-sections">
            <section class="card content-card">
                <div class="card-title">Recent exercise</div>
                <div class="table-list activity-admin-table exercise-admin-table" style="margin-top: 14px;">
                    <div class="table-row header">
                        <span>User</span>
                        <span>Activity</span>
                        <span>Duration</span>
                        <span>Calories</span>
                        <span>Date</span>
                        <span>Actions</span>
                    </div>

                    @forelse ($activities as $item)
                        <div class="table-row">
                            <span class="table-primary">{{ optional($item->user)->name ?? 'Unknown user' }}</span>
                            <span class="table-primary">{{ $item->activity }}</span>
                            <span class="table-secondary">{{ $item->duration_minutes }} min</span>
                            <span class="table-secondary">{{ $item->calories_burned ?: '—' }} kcal</span>
                            <span class="table-secondary">{{ optional($item->logged_at)->format('M d, Y · g:i A') ?: '—' }}</span>
                            <span class="record-actions">
                                <form method="POST" action="{{ route('admin.exercise.delete', $item) }}" onsubmit="return confirm('Delete this exercise record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="primary-button delete-button" type="submit">Delete</button>
                                </form>
                            </span>
                        </div>
                    @empty
                        <div class="empty-state" style="grid-column: 1 / -1; min-height: 120px;">
                            <h3>No activity logs</h3>
                        </div>
                    @endforelse
                </div>
                <div style="margin-top: 18px;">{{ $activities->links() }}</div>
            </section>

            <section class="card content-card">
                <div class="card-title">Medication plans</div>
                <div class="table-list activity-admin-table medication-admin-table" style="margin-top: 14px;">
                    <div class="table-row header">
                        <span>User</span>
                        <span>Medication</span>
                        <span>Dose</span>
                        <span>Instructions</span>
                        <span>Scheduled</span>
                        <span>Status</span>
                        <span>Actions</span>
                    </div>

                    @forelse ($medications as $item)
                        <div class="table-row">
                            <span class="table-primary">{{ optional($item->user)->name ?? 'Unknown user' }}</span>
                            <span class="table-primary">{{ $item->name }}</span>
                            <span class="table-secondary">{{ $item->dose ?: '—' }}</span>
                            <span class="table-secondary">{{ $item->instructions ?: '—' }}</span>
                            <span class="table-secondary">{{ optional($item->scheduled_at)->format('M d, Y · g:i A') ?: '—' }}</span>
                            <span><span class="status-badge {{ $item->active ? 'green' : 'orange' }}">{{ $item->active ? 'Active' : 'Inactive' }}</span></span>
                            <span class="record-actions">
                                <form method="POST" action="{{ route('admin.medications.delete', $item) }}" onsubmit="return confirm('Delete this medication record?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="primary-button delete-button" type="submit">Delete</button>
                                </form>
                            </span>
                        </div>
                    @empty
                        <div class="empty-state" style="grid-column: 1 / -1; min-height: 120px;">
                            <h3>No medication records</h3>
                        </div>
                    @endforelse
                </div>
                <div style="margin-top: 18px;">{{ $medications->links() }}</div>
            </section>
        </div>
    </div>
</x-app-layout>
