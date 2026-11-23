<style>
    #add-admin .admin-create-form input,
    #add-admin .admin-create-form select,
    .user-search-form .search-box input,
    .user-search-form > select,
    .role-action-form > select {
        border: 1px solid #e4eaf2 !important;
        border-radius: 10px !important;
        background: #fff;
    }
    #add-admin .admin-create-form .primary-button { margin-left: 16px !important; }
    .crud-actions > .secondary-button {
        border: 1px solid #2f80ed !important;
        border-radius: 8px !important;
        background: #2f80ed !important;
        color: #fff !important;
        box-shadow: 0 5px 10px rgba(47,128,237,.14);
    }
    .crud-actions > .secondary-button:hover {
        border-color: #1e64d6 !important;
        background: #1e64d6 !important;
    }
    .admin-profile-dialog { position: fixed !important; inset: 0 !important; width: min(92vw, 460px) !important; max-height: 90vh; margin: auto !important; padding: 0 !important; border: 0 !important; border-radius: 16px !important; overflow: auto; box-shadow: 0 24px 70px rgba(25,50,80,.25); }
    .admin-profile-dialog::backdrop { background: rgba(23,43,77,.48); }
    .admin-profile-dialog .admin-profile-form { display: grid !important; grid-template-columns: 1fr 1fr; gap: 13px; padding: 22px; position: static; width: auto; border: 0; box-shadow: none; }
    .admin-profile-dialog .dialog-header, .admin-profile-dialog .profile-photo-section, .admin-profile-dialog .dialog-section-title, .admin-profile-dialog .dialog-actions { grid-column: 1 / -1; }
    .admin-profile-dialog .dialog-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf1f6; padding-bottom: 14px; }
    .admin-profile-dialog .dialog-header strong { color: #203b5d; font-size: 15px; }
    .admin-profile-dialog .dialog-header span, .admin-profile-dialog small { display: none; }
    .admin-profile-dialog .dialog-field { display: grid; gap: 5px; }
    .admin-profile-dialog .dialog-field label { color: #657994; font-size: 10px; font-weight: 700; }
    .admin-profile-dialog .dialog-field input, .admin-profile-dialog .dialog-field select { width: 100%; box-sizing: border-box; border: 1px solid #e4eaf2; border-radius: 8px; padding: 10px; }
    .admin-profile-dialog .profile-photo-section { display: flex; align-items: center; gap: 12px; }
    .admin-profile-dialog .profile-photo-preview, .admin-profile-dialog .profile-photo-placeholder { width: 58px; height: 58px; flex: 0 0 58px; border-radius: 50%; object-fit: cover; }
    .admin-profile-dialog .dialog-actions { display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid #edf1f6; padding-top: 15px; }
    .admin-profile-dialog .dialog-close { border: 0; border-radius: 50%; background: #f3f6fa; cursor: pointer; font-size: 18px; width: 28px; height: 28px; }
    @media (max-width: 600px) { .admin-profile-dialog .admin-profile-form { grid-template-columns: 1fr; } .admin-profile-dialog .dialog-header, .admin-profile-dialog .profile-photo-section, .admin-profile-dialog .dialog-section-title, .admin-profile-dialog .dialog-actions { grid-column: 1; } }
</style>
<x-app-layout>
    <div class="page-container">
        <div class="page-heading"><div><p class="eyebrow">Administration</p><h1>User management</h1><p>Create administrators and manage protected admin profiles.</p></div></div>
        @if (session('status'))<div class="alert-success">{{ session('status') }}</div>@endif
        @if ($errors->any())<div class="alert-error">{{ $errors->first() }}</div>@endif
        <div class="summary-grid" style="margin-bottom:17px;"><section class="card summary-card"><div class="summary-label">All accounts</div><div class="summary-value">{{ $users->total() }}</div></section><section class="card summary-card"><div class="summary-label">Users</div><div class="summary-value">{{ $userCount }}</div></section><section class="card summary-card"><div class="summary-label">Administrators</div><div class="summary-value">{{ $adminCount }}</div></section></div>
        <section id="add-admin" class="card content-card" style="margin-bottom:17px;"><div class="card-title">Add another administrator</div><div class="card-subtitle" style="margin:5px 0 15px;">Create a separate administrator account for your team.</div><form method="POST" action="{{ route('admin.users.admin.create') }}" class="admin-create-grid admin-create-form">@csrf<input name="name" aria-label="Full name" placeholder="Full name" required><input name="email" type="email" aria-label="Email address" placeholder="Email address" required><input name="password" type="password" aria-label="Password" placeholder="Password" required><input name="password_confirmation" type="password" aria-label="Confirm password" placeholder="Confirm password" required><select name="gender" aria-label="Gender"><option value="">Gender (optional)</option><option>Female</option><option>Male</option><option>Other</option></select><button class="primary-button" type="submit">Create admin</button></form></section>
        <section class="card content-card"><div class="list-header"><div><div class="card-title">Registered users</div><div class="card-subtitle" style="margin-top:4px;">Administrator profiles contain account information only.</div></div></div>
            <form method="GET" action="{{ route('admin.users.index') }}" class="page-toolbar user-search-form" style="margin:16px 0;align-items:center;"><div class="search-box"><input id="user-search" type="search" name="search" value="{{ $search }}" placeholder="Search name or email" aria-label="Search users"><button type="submit" class="search-button" aria-label="Search users" title="Search"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="6.5"></circle><path d="m16 16 5 5"></path></svg></button></div><select name="role" aria-label="Filter by role"><option value="all" @selected($role === 'all')>All roles</option><option value="user" @selected($role === 'user')>Users only</option><option value="admin" @selected($role === 'admin')>Admins only</option></select><button type="submit" class="secondary-button">Filter</button></form>
            <div class="table-list"><div class="table-row header"><span>User</span><span>Joined</span><span>Role</span><span>Actions</span></div>
                @forelse ($users as $user)
                    <div class="table-row"><span class="user-cell">@if($user->profile_photo_path)<img class="admin-avatar" src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="{{ $user->name }}">@endif<span><span class="table-primary block">{{ $user->name }}</span><span class="table-secondary block">{{ $user->email }}</span></span></span><span class="table-secondary">{{ $user->created_at->format('M d, Y') }}</span><span><span class="status-badge {{ $user->isAdmin() ? 'orange' : 'green' }}">{{ ucfirst($user->role) }}</span></span><span class="crud-actions">@if ($user->isAdmin())<button type="button" class="secondary-button" onclick="document.getElementById('admin-profile-{{ $user->id }}').showModal()">Edit profile</button><dialog id="admin-profile-{{ $user->id }}" class="admin-profile-dialog"><form method="POST" action="{{ route('admin.users.profile.update', $user) }}" enctype="multipart/form-data" class="admin-profile-form">@csrf @method('PATCH')<div class="dialog-header"><div><strong>Edit admin profile</strong><span>Update account details</span></div><button type="button" class="dialog-close" aria-label="Close" onclick="this.closest('dialog').close()">&times;</button></div><div class="profile-photo-section">@if($user->profile_photo_path)<img class="profile-photo-preview" src="{{ asset('storage/'.$user->profile_photo_path) }}" alt="Current profile photo">@else<span class="profile-photo-placeholder">{{ strtoupper(substr($user->name, 0, 1)) }}</span>@endif<label class="photo-upload-label">Change photo<input name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG, or WebP · maximum 2 MB</small></label></div><div class="dialog-field"><label for="admin-name-{{ $user->id }}">Full name</label><input id="admin-name-{{ $user->id }}" name="name" value="{{ $user->name }}" required></div><div class="dialog-field"><label for="admin-email-{{ $user->id }}">Email address</label><input id="admin-email-{{ $user->id }}" name="email" type="email" value="{{ $user->email }}" required></div><div class="dialog-field"><label for="admin-gender-{{ $user->id }}">Gender</label><select id="admin-gender-{{ $user->id }}" name="gender"><option value="">Not specified</option><option @selected($user->gender === 'Female')>Female</option><option @selected($user->gender === 'Male')>Male</option><option @selected($user->gender === 'Other')>Other</option></select></div><div class="dialog-section-title">Change password <span>Optional</span></div><div class="dialog-field"><label for="admin-password-{{ $user->id }}">New password</label><input id="admin-password-{{ $user->id }}" name="password" type="password" placeholder="Leave blank to keep current password"></div><div class="dialog-field"><label for="admin-password-confirm-{{ $user->id }}">Confirm new password</label><input id="admin-password-confirm-{{ $user->id }}" name="password_confirmation" type="password"></div><div class="dialog-actions"><button type="button" class="secondary-button" onclick="this.closest('dialog').close()">Cancel</button><button class="primary-button" type="submit">Save changes</button></div></form></dialog>@else<form method="POST" action="{{ route('admin.users.role', $user) }}" x-data="{ role: '{{ $user->role }}' }" class="role-action-form">@csrf @method('PATCH')<select name="role" x-model="role" aria-label="Role for {{ $user->email }}"><option value="user">User</option><option value="admin">Admin</option></select><button type="submit" class="primary-button" x-show="role === 'admin'" x-cloak>Save as admin</button></form>@endif</span></div>
                @empty
                    <div class="empty-state" style="grid-column:1/-1;min-height:140px;"><h3>No users found</h3><p>Try another name, email, or role filter.</p></div>
                @endforelse
            </div><div style="margin-top:18px;">{{ $users->links() }}</div>
            @if($search)<script>document.addEventListener('DOMContentLoaded',()=>{const q=@json(strtolower($search));const list=document.querySelector('.table-list');if(!list||!q)return;const esc=s=>s.replace(/[.*+?^${}()|[\]\\]/g,'\\$&');[...list.querySelectorAll('.table-row:not(.header)')].forEach(row=>{const text=row.textContent.toLowerCase();if(text.includes(q)){row.classList.add('search-match');list.insertBefore(row,list.children[1]||null);row.querySelectorAll('span').forEach(el=>{if(el.children.length===0&&el.textContent.toLowerCase().includes(q))el.innerHTML=el.textContent.replace(new RegExp(esc(q),'ig'),m=>'<mark>'+m+'</mark>')})}})});</script>@endif
        </section>
    </div>
</x-app-layout>
