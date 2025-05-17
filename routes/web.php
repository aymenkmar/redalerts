<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Landing page
Route::get('/', \App\Livewire\LandingPage::class);

// Authentication routes
Route::get('/login', \App\Livewire\LoginPage::class)->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    // Main Dashboard (similar to frontend)
    Route::get('/main-dashboard', \App\Livewire\MainDashboard::class)->name('main-dashboard');

    // Admin Dashboard (original Laravel dashboard)
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');

    Route::get('/auth-test', function() {
        return view('api-test');
    });

    // API Management page (placeholder for now)
    Route::get('/api-management', function() {
        return view('dashboard'); // Using dashboard view as placeholder
    });
});
