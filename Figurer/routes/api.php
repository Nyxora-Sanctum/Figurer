<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\AIController;

// Public routes to access login and register
Route::post('/register', [AuthController::class, 'register']);
Route::post(('/login'), [AuthController::class,'login'])->name('login');


// Both user and admin can access
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/prompt', [AIController::class, 'AIOutput']);
});

// Only user can access
Route::middleware('auth:sanctum', 'CheckRole:user')->group(function () {
    Route::post('/buy', [PaymentController::class,'payment']);
    Route::middleware('auth:sanctum')->get('/transaction/{id}', [PaymentController::class, 'getTransaction']);
});

// Only admin can access
Route::middleware('auth:sanctum', 'CheckRole:admin')->group(function () {

});