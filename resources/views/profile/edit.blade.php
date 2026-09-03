<x-app-layout>
    <div class="page-container">
        <div class="page-heading"><div><p class="eyebrow">Track</p><h1>Profile</h1><p>Your personal health information and care preferences.</p></div><a href="{{ route('dashboard') }}" class="secondary-button">Back to dashboard</a></div>
        @if (session('status') === 'profile-updated')<div class="alert-success">Your profile has been updated successfully.</div>@endif
        @if ($errors->any())<div class="alert-success" style="border-color:#f2c4c0;background:#fff6f5;color:#bf554e;">Please check the highlighted fields and try again.</div>@endif
        <section class="card content-card" style="margin-top:0;">
            <div class="profile-identity"><span class="profile-avatar">{{ collect(explode(' ', trim($user->name)))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->implode('') ?: 'DC' }}</span><div><div class="profile-name">{{ $user->name }}</div><div class="profile-email">{{ $user->email }}</div></div><span class="status-badge {{ $user->glucose_target_min ? 'green' : 'orange' }}" style="margin-left:auto;">{{ $user->glucose_target_min ? 'Profile active' : 'Complete your profile' }}</span></div>
            <form method="POST" action="{{ route('profile.update') }}" class="profile-form">
                @csrf
                @method('PATCH')
                <div class="form-field"><label for="name">Full name</label><input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autocomplete="name">@error('name')<div class="form-help" style="color:#c4564f;">{{ $message }}</div>@enderror</div>
                <div class="form-field"><label for="email">Email address</label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="email">@error('email')<div class="form-help" style="color:#c4564f;">{{ $message }}</div>@enderror</div>
                <div class="form-field"><label for="glucose_target_min">Glucose target min (mg/dL)</label><input id="glucose_target_min" name="glucose_target_min" type="number" min="40" max="300" value="{{ old('glucose_target_min', $user->glucose_target_min) }}" placeholder="80"></div>
                <div class="form-field"><label for="glucose_target_max">Glucose target max (mg/dL)</label><input id="glucose_target_max" name="glucose_target_max" type="number" min="40" max="400" value="{{ old('glucose_target_max', $user->glucose_target_max) }}" placeholder="130"></div>
                <div class="form-field"><label for="height_cm">Height (cm)</label><input id="height_cm" name="height_cm" type="number" step="0.01" min="50" max="250" value="{{ old('height_cm', $user->height_cm) }}" placeholder="170"></div>
                <div class="form-field"><label for="weight_kg">Weight (kg)</label><input id="weight_kg" name="weight_kg" type="number" step="0.01" min="2" max="500" value="{{ old('weight_kg', $user->weight_kg) }}" placeholder="70"></div>
                <div class="form-field"><label for="emergency_contact_name">Emergency contact name</label><input id="emergency_contact_name" name="emergency_contact_name" type="text" value="{{ old('emergency_contact_name', $user->emergency_contact_name) }}" placeholder="Contact name"></div>
                <div class="form-field"><label for="emergency_contact_phone">Emergency contact phone</label><input id="emergency_contact_phone" name="emergency_contact_phone" type="text" value="{{ old('emergency_contact_phone', $user->emergency_contact_phone) }}" placeholder="Phone number"></div>
                <div class="form-field"><label for="doctor_name">Doctor / clinic</label><input id="doctor_name" name="doctor_name" type="text" value="{{ old('doctor_name', $user->doctor_name) }}" placeholder="Care provider"></div>
                <div class="form-field"><label for="health_goals">Health goals</label><textarea id="health_goals" name="health_goals" placeholder="For example: walk 30 minutes daily, keep readings in target range">{{ old('health_goals', $user->health_goals) }}</textarea><div class="form-help">These goals help shape the reminders shown in your dashboard.</div></div>
                <div class="form-field full"><label for="medical_notes">Medical notes</label><textarea id="medical_notes" name="medical_notes" placeholder="Optional notes to keep with your personal care record">{{ old('medical_notes', $user->medical_notes) }}</textarea></div>
                <div class="form-actions"><a href="{{ route('dashboard') }}" class="secondary-button">Cancel</a><button type="submit" class="primary-button">Save profile</button></div>
            </form>
        </section>
        <section class="card content-card"><div class="card-title">Account security</div><p class="card-subtitle" style="line-height:1.7;margin-top:8px;">Use the standard Laravel authentication screens to change your password or delete your account. Add those actions here when you are ready to expose them in the redesigned profile flow.</p></section>
    </div>
    <button type="button" class="chat-fab" aria-label="Open DiaCare assistant"><span class="chat-dot"></span><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 11.5a7.5 7.5 0 0 1-8 7.5 8.8 8.8 0 0 1-3.3-.63L4 20l1.64-3.62A7.4 7.4 0 0 1 4.5 12 7.5 7.5 0 0 1 12 4.5h.5A7.5 7.5 0 0 1 20 11.5Z"/><path d="M9 12h.01M12 12h.01M15 12h.01"/></svg></button>
</x-app-layout>
