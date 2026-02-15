<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Inertia\Inertia;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Social Auth Routes
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider'])->where('provider', 'google|yandex')->name('social.auth');
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->where('provider', 'google|yandex');
