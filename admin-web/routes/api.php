<?php

use App\Http\Controllers\API\AttendanceController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\HealthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\TaskController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'check']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [UserController::class, 'show']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    
    // Attendance routes
    Route::get('/attendances', [AttendanceController::class, 'index']);
    Route::post('/attendances/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendances/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendances/today', [AttendanceController::class, 'todayStatus']);
    Route::get('/attendances/history', [AttendanceController::class, 'history']);
    Route::get('/attendances/export', [AttendanceController::class, 'exportUser']);

    // Task routes
    Route::get('/tasks', [TaskController::class, 'index']); // Admin: semua tugas
    Route::post('/tasks', [TaskController::class, 'store']); // Admin: buat tugas baru
    Route::get('/tasks/available', [TaskController::class, 'available']); // User: tugas tersedia
    Route::get('/tasks/my-tasks', [TaskController::class, 'myTasks']); // User: tugas saya
    Route::post('/tasks/{id}/accept', [TaskController::class, 'accept']); // User: terima tugas
    Route::post('/tasks/{id}/reject', [TaskController::class, 'reject']); // User: tolak tugas
    Route::post('/tasks/{id}/complete', [TaskController::class, 'complete']); // User: selesaikan tugas
});