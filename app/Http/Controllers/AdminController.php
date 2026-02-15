<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\PlayedTrack;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;


class AdminController extends Controller
{
    public function index()
    {
        // Simple stats
        $stats = [
            'users_count' => User::count(),
            'played_tracks_count' => PlayedTrack::count(),
            'queue_jobs' => \DB::table('jobs')->count(),
        ];

        // Read last 50 lines of laravel.log
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        if (File::exists($logPath)) {
            $file = file($logPath);
            $logs = array_slice($file, -50);
            $logs = array_reverse($logs); // Newest first
        }

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'logs' => $logs,
        ]);
    }
}
