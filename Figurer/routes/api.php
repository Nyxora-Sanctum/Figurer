<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Middleware\CheckRole;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\AIController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\api\TemplateControlller;

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
    
    // User Management routes
    Route::get('/user/profile', [UserController::class, 'getCurrentProfile']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);

    // Template Management routes
    Route::get('/templates/owned-templates', [TemplateControlller::class, 'getAllOwned']);
    Route::get('/templates/all-templates', [TemplateControlller::class, 'getAllTemplates']);
    Route::get('/templates/{id}', [TemplateControlller::class, 'getByID']);

    // Payment routes
    Route::post('/transaction/buy', [PaymentController::class,'payment']);
    Route::get('/transaction/{id}', [PaymentController::class, 'getTransaction']);
});

// Only admin can access
Route::middleware('auth:sanctum', 'CheckRole:admin')->group(function () {
    // Template Management routes
    Route::post('/templates', [TemplateControlller::class, 'create']);
    Route::patch('/templates/{id}', [TemplateControlller::class, 'patch']);
    Route::delete('/templates/{id}', [TemplateControlller::class, 'delete']);
});