<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MealPhoto;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('admin.dashboard', [
            'title' => 'Admin Dashboard · DiaCare',
            'userCount' => User::count(),
            'adminCount' => User::where('role', 'admin')->count(),
            'mealAnalysisCount' => MealPhoto::where('status', 'complete')->count(),
            'recentUsers' => User::query()->latest('id')->limit(8)->get(),
        ]);
    }
}
