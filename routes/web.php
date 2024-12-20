<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\API\TimetableController;
use App\Http\Controllers\API\SavedScheduleController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public routes
Route::get('hello', function () {
    return Inertia::render('Hello');
});

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// Timetable routes (no auth required)
Route::prefix('timetables')->group(function () {
    Route::get('/', [TimetableController::class, 'index'])
        ->name('timetables.index');
    Route::get('/{courseCode}/{term}/{year}', [TimetableController::class, 'show'])
        ->name('timetables.show');
    Route::post('/search', [TimetableController::class, 'search'])
        ->name('timetables.search');
    Route::post('/check-conflicts', [TimetableController::class, 'checkConflicts'])
        ->name('timetables.check-conflicts');
    Route::post('/check-section-selectable', [TimetableController::class, 'checkSectionSelectable'])
        ->name('timetables.check-section-selectable');
});

// Auth required routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Saved schedules routes
    Route::resource('schedules', SavedScheduleController::class);
});

require __DIR__ . '/auth.php';
