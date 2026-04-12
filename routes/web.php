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

// WhatsApp Service Routes
use App\Http\Controllers\WhatsappController;
Route::get('/whatsapp', [WhatsappController::class, 'index'])->name('whatsapp.index');
Route::post('/whatsapp', [WhatsappController::class, 'store'])->name('whatsapp.store');
Route::get('/whatsapp/{project}', [WhatsappController::class, 'show'])->name('whatsapp.show');
Route::delete('/whatsapp/{project}', [WhatsappController::class, 'destroy'])->name('whatsapp.destroy');

Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
