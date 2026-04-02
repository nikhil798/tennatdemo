<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\LandlordDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Landlord\Auth\AuthenticatedSessionController as LandlordAuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/landlord')->name('home');

Route::prefix('landlord')->name('landlord.')->group(function () {
    Route::middleware('guest:landlord')->group(function () {
        Route::get('/login', [LandlordAuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('/login', [LandlordAuthenticatedSessionController::class, 'store'])->name('login.store');
    });

    Route::middleware('auth:landlord')->group(function () {
        Route::get('/', LandlordDashboardController::class)->name('dashboard');
        Route::post('/logout', [LandlordAuthenticatedSessionController::class, 'destroy'])->name('logout');
    });
});

Route::middleware(['tenant:optional', 'guest'])->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
});

Route::middleware('tenant')->group(function () {
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
        Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
        Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
        Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
        Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
        Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
