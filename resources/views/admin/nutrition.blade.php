<style>
    .nutrition-admin-table .table-row {
        column-gap: 3px;
        grid-template-columns: 105px 165px minmax(200px, 1fr) 95px 72px;
    }
</style>
<x-app-layout>
    <div class="page-container">
        <div class="page-heading">
            <div>
                <p class="eyebrow">Administration</p>
                <h1>Food &amp; nutrition</h1>
                <p>Review saved meal analyses and nutrition estimates by account.</p>
            </div>
        </div>

        @if (session('status'))
            <div class="alert-success">{{ session('status') }}</div>
        @endif

        <section class="card content-card">
            <div class="table-list five-columns nutrition-admin-table">
                <div class="table-row header">
                    <span>User</span>
                    <span>Meal</span>
                    <span>Nutrition</span>
                    <span>Saved</span>
                    <span>Actions</span>
                </div>

                @forelse ($meals as $meal)
                    <div class="table-row">
                        <span>
                            <span class="table-primary block">{{ $meal->user->name }}</span>
                            <span class="table-secondary block">{{ $meal->user->email }}</span>
                        </span>
                        <span class="table-primary">{{ data_get($meal->analysis, 'meal_name', 'Meal analysis') }}</span>
                        <span class="table-secondary">{{ data_get($meal->analysis, 'calories', '—') }} kcal · {{ data_get($meal->analysis, 'carbohydrates_g', '—') }} g carbs</span>
                        <span class="table-secondary">{{ $meal->created_at->format('M d, Y') }}</span>
                        <span class="crud-actions">
                            <form method="POST" action="{{ route('admin.nutrition.delete', $meal) }}" onsubmit="return confirm('Delete this meal analysis?')">
                                @csrf
                                @method('DELETE')
                                <button class="primary-button delete-button" type="submit" aria-label="Delete meal analysis">
                                    Delete
                                </button>
                            </form>
                        </span>
                    </div>
                @empty
                    <div class="empty-state" style="grid-column: 1 / -1; min-height: 150px;">
                        <h3>No saved meal analyses</h3>
                    </div>
                @endforelse
            </div>

            <div style="margin-top: 18px;">
                {{ $meals->links() }}
            </div>
        </section>
    </div>
</x-app-layout>
