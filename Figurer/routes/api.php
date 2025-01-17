<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\API\AIController;

Route::post('/register', [AuthController::class, 'register']);
Route::post(('/login'), [AuthController::class,'login'])->name('login');

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:sanctum', 'CheckRole:user')->group(function () {
    Route::post('/prompt', [AIController::class, 'AIOutput']);
});

