<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\LogController;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::post('/dashboard/test-connection', [DashboardController::class, 'testConnection'])->name('dashboard.test');


Route::resource('projects', ProjectController::class);
Route::post('projects/{project}/toggle', [ProjectController::class, 'toggle'])->name('projects.toggle');

Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
