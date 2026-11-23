<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserManagementController extends Controller
{
    public function createAdmin(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'gender' => ['nullable', 'string', 'max:30'],
        ]);
        $data['role'] = 'admin';
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return back()->with('status', 'New admin account created.');
    }

    public function updateAdminProfile(Request $request, User $user): RedirectResponse
    {
        if (! $user->isAdmin()) return back()->withErrors(['user' => 'Only admin profiles can be edited here.']);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'gender' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        if (blank($data['password'] ?? null)) unset($data['password']); else $data['password'] = Hash::make($data['password']);
        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path);
            $data['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }
        unset($data['profile_photo']);
        $user->update($data);
        return back()->with('status', 'Admin profile updated.');
    }

    public function index(Request $request): View
    {
        $role = $request->input('role', 'all');
        if (! in_array($role, ['all', 'user', 'admin'], true)) $role = 'all';
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->input('search'));
                $query->where(fn ($nested) => $nested->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($role !== 'all', fn ($query) => $query->where('role', $role))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'title' => 'User Management · DiaCare',
            'users' => $users,
            'search' => (string) $request->input('search', ''),
            'role' => $role,
            'userCount' => User::where('role', 'user')->count(),
            'adminCount' => User::where('role', 'admin')->count(),
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate(['role' => ['required', 'in:user,admin']]);

        if ($user->isAdmin() && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Admin accounts are protected and cannot be changed to User.']);
        }

        $user->update(['role' => $validated['role']]);

        return back()->with('status', 'User role updated.');
    }
}
