<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Citizen dashboard: status summary and latest complaints.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard', [
            'statusCounts' => $user->complaints()->toBase()
                ->selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'latestComplaints' => $user->complaints()->with(['category', 'district'])->latest()->limit(5)->get(),
            'notifications' => $user->notifications()->latest()->limit(5)->get(),
            'unreadNotificationCount' => $user->unreadNotifications()->count(),
        ]);
    }
}
