<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TaskWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Attendance management routes
    Route::resource('attendances', AttendanceController::class)->only(['index', 'show']);
    Route::get('/attendances-export', [AttendanceController::class, 'export'])->name('attendances.export');
    Route::post('/attendances-import', [AttendanceController::class, 'import'])->name('attendances.import');
    Route::get('/attendances-print', [AttendanceController::class, 'print'])->name('attendances.print');
    
    // Task management routes
    Route::resource('tasks', TaskWebController::class);
});
