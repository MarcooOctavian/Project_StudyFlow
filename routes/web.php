<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/tasks', [DashboardController::class, 'createTask'])->name('dashboard.tasks.create');
    Route::patch('/dashboard/tasks/{id}/toggle', [DashboardController::class, 'toggleTask'])->name('dashboard.tasks.toggle');
    Route::delete('/dashboard/tasks/{id}', [DashboardController::class, 'deleteTask'])->name('dashboard.tasks.delete');
    Route::post('/dashboard/notes', [DashboardController::class, 'saveNote'])->name('dashboard.notes.save');
    Route::delete('/dashboard/notes/{id}', [DashboardController::class, 'deleteNote'])->name('dashboard.notes.delete');
    Route::post('/dashboard/pomodoro/complete', [DashboardController::class, 'completePomodoro'])->name('dashboard.pomodoro.complete');
    Route::post('/dashboard/pomodoro/settings', [DashboardController::class, 'updatePomodoroSettings'])->name('dashboard.pomodoro.settings');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
