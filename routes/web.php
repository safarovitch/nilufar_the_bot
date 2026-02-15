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
})->name('home');

Route::middleware([
    'auth',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('/settings/profile', [App\Http\Controllers\Settings\ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings/profile', [App\Http\Controllers\Settings\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/settings/profile', [App\Http\Controllers\Settings\ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/settings/password', [App\Http\Controllers\Settings\PasswordController::class, 'edit'])->name('user-password.edit');
    Route::put('/settings/password', [App\Http\Controllers\Settings\PasswordController::class, 'update'])->name('user-password.update');

    Route::get('/settings/two-factor', [App\Http\Controllers\Settings\TwoFactorAuthenticationController::class, 'show'])->name('two-factor.show');

    Route::get('/settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');
});

// Social Auth Routes
Route::get('/auth/{provider}', [AuthController::class, 'redirectToProvider'])->where('provider', 'google|yandex')->name('social.auth');
Route::get('/auth/{provider}/callback', [AuthController::class, 'handleProviderCallback'])->where('provider', 'google|yandex');

// Admin Routes
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::get('/', [App\Http\Controllers\AdminController::class, 'index'])->name('admin.dashboard');
});
