<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\AIController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\api\TemplateControlller;

// Public routes to access login and register
Route::post('/register', [AuthController::class, 'register']);
Route::post(('/login'), [AuthController::class,'login'])->name('login');


// Both user and admin can access
Route::middleware(['auth:sanctum'])->group(function () {
    // Template Routes
    Route::get('/templates/all-templates', [TemplateControlller::class, 'getAllTemplates']);
    Route::get('/templates/{id}', [TemplateControlller::class, 'getByID']);

    // Logout Routes
    Route::post('/logout', [AuthController::class, 'logout']);

    // AI Routes
    Route::post('/prompt', [AIController::class, 'AIOutput']);
});

// Only user can access
Route::middleware('auth:sanctum', 'CheckRole:user')->group(function () {
    
    // User Management routes
    Route::get('/user/profile', [UserController::class, 'getCurrentProfile']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);

    // Template Management routes
    Route::get('/templates/owned-templates', [TemplateControlller::class, 'getAllOwned']);

    // Payment routes
    Route::post('/transaction/buy', [PaymentController::class,'payment']);
    Route::get('/transaction/{id}', [PaymentController::class, 'getTransaction']);
});

// Only admin can access
Route::middleware('auth:sanctum', 'CheckRole:admin')->group(function () {
    // Template Management Routes
    Route::post('/admin/templates', [TemplateControlller::class, 'create']);
    Route::patch('/admin/templates/{id}', [TemplateControlller::class, 'patch']);
    Route::delete('/admin/templates/{id}', [TemplateControlller::class, 'delete']);

    // User Management Routes
    Route::delete('/admin/user/{id}', [UserController::class, 'deleteAccount']);

    // Payment Management Routes
    Route::get('/admin/transactions', [PaymentController::class, 'getAllTransactions']);
    Route::get('/admin/transactions/{id}', [PaymentController::class, 'getTransaction']);
    Route::patch('/admin/transactions/{id}', [PaymentController::class, 'updateTransaction']);
    Route::get('/admin/invoices', [PaymentController::class, 'getInvoices']);
    Route::get('/admin/invoices/{id}', [PaymentController::class, 'getInvoice']);
});