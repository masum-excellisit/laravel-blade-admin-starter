<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Post;
use App\Models\User;
use App\Services\DashboardAnalytics;

class DashboardController extends Controller
{
    public function index(DashboardAnalytics $analytics)
    {
        // $analytics->all() supplies $kpis, $trend, $contentMix, $engagement, $system.
        return view('admin.dashboard', $analytics->all() + [
            'recentPosts' => Post::latest()->take(6)->get(),
            'recentUsers' => User::customers()->latest()->take(6)->get(),
            'recentActivity' => ActivityLog::with('user')->latest()->take(8)->get(),
        ]);
    }
}
