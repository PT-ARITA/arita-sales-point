<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Index route
Route::get('/', function () {
    // Check if the user is authenticated
    if (Auth::check()) {
        $user = Auth::user();

        // Check the user's role and redirect accordingly
        if ($user->hasRole('superadmin')) {
            // You can store the selected role in the session to redirect accordingly
            $role = session('role', 'admin'); // Default role for superadmin is 'admin'

            if ($role === 'admin') {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('user.dashboard');
            }
        } elseif ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        } elseif ($user->hasRole('user')) {
            return redirect()->route('user.dashboard');
        }
    }

    // If the user is not authenticated, redirect to the login page
    return redirect()->route('login');
});

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'login')->name('login')->middleware('guest');
    Route::post('/auth', 'auth')->name('auth');
    Route::get('/logout', 'logout')->name('logout');
});

// Admin Dashboard Routes
Route::middleware(['auth', 'role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'admin')->name('dashboard');
    });

    // CRUD User Marketing
    Route::get('/marketing/data', [UserController::class, 'data'])->name('marketing.data');
    Route::resource('marketing', UserController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    // CRUD Company Profile
    Route::get('/company/data', [CompanyProfileController::class, 'data'])->name('company.data');
    Route::resource('company', CompanyProfileController::class);
});

// User Dashboard Routes
Route::middleware(['auth', 'role:user|superadmin'])->prefix('user')->name('user.')->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('/', 'user')->name('dashboard');
    });
});
