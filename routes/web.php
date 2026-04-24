<?php

use App\Http\Controllers\StatisticsController;
use Illuminate\Support\Facades\Route;

Route::get('/', [StatisticsController::class, 'dashboard'])->name('public.dashboard');
Route::get('/fakultet/{slug}', [StatisticsController::class, 'faculty'])->name('public.faculty');
Route::get('/kafedra/{slug}', [StatisticsController::class, 'department'])->name('public.department');
Route::get('/kurator/{slug}', [StatisticsController::class, 'curator'])->name('public.curator');
Route::get('/guruh/{slug}', [StatisticsController::class, 'group'])->name('public.group');
