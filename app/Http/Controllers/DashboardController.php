<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $totalUsers = User::where('role', 'user')->count();
            $today = now()->toDateString();
            $todayAttendance = Attendance::whereDate('date', $today)->count();
            $todayHadir = Attendance::whereDate('date', $today)->where('status', 'hadir')->count();

            return view('dashboard.admin', compact('totalUsers', 'todayAttendance', 'todayHadir'));
        }

        $todayRecord = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        $recentAttendances = Attendance::where('user_id', $user->id)
            ->orderByDesc('date')
            ->take(5)
            ->get();

        return view('dashboard.user', compact('todayRecord', 'recentAttendances'));
    }
}
