<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\SurveyController;

// Public routes
Route::get('/', function () {
    return view('home');
})->name('home');

// Guest routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Protected routes (only accessible when logged in)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Contacts (Students)
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{student}', [ContactController::class, 'show'])->name('contacts.show');

    // Surveys
    Route::get('/surveys', [SurveyController::class, 'index'])->name('surveys.index');

    // Resources (placeholder)
    Route::get('/resources', function () {
        return view('resources.index');
    })->name('resources.index');

    // Reports (placeholder)
    Route::get('/reports', function () {
        return view('reports.index');
    })->name('reports.index');

    // Settings (placeholder)
    Route::get('/settings', function () {
        return view('settings.index');
    })->name('settings.index');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
