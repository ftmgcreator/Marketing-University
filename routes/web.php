<?php

use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'rahbariyat'])->group(function () {
    Route::get('/', [StatisticsController::class, 'dashboard'])->name('public.dashboard');
    Route::get('/kafedra/{slug}', [StatisticsController::class, 'department'])->name('public.department');
    Route::get('/kurator/{slug}', [StatisticsController::class, 'curator'])->name('public.curator');
    Route::get('/guruh/{slug}', [StatisticsController::class, 'group'])->name('public.group');
});

Route::middleware(['auth', 'super'])->group(function () {
    Route::get('/fakultet/{slug}', [StatisticsController::class, 'faculty'])->name('public.faculty');
});
