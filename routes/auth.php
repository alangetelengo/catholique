<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\EnsureGuestAuthLocale;
use Illuminate\Support\Facades\Route;

Route::middleware(['guest', EnsureGuestAuthLocale::class])->group(function (): void {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');
