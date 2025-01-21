<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\AIController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\TemplateController;

// Public routes to access login and register
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post(('/auth/login'), [AuthController::class,'login'])->name('login');


// Both user and admin can access
Route::middleware(['auth:sanctum'])->group(function () {
    // Template Routes
    Route::get('/templates/get/all-templates', [TemplateController::class, 'getAllTemplates']);
    Route::get('/templates/inventory/get/{id}', [TemplateController::class, 'getByID']);
    Route::get('/templates/inventory/all-used', [TemplateController::class, 'getAllUsed']);

    // Logout Routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // AI Routes
    Route::post('/ai/prompt', [AIController::class, 'AIOutput']);
});

// Only user can access
Route::middleware('auth:sanctum', 'CheckRole:user')->group(function () {
    
    // User Management routes
    Route::get('/user/profile', [UserController::class, 'getCurrentProfile']);
    Route::patch('/user/profile', [UserController::class, 'updateProfile']);

    // Template Management routes
    Route::get('/templates/inventory', [TemplateController::class, 'getAllOwned']);
    Route::post('/templates/use/{id}', [TemplateController::class,'useTemplate']);

    // Payment routes
    Route::post('/transaction/buy', [PaymentController::class,'payment']);
    Route::get('/transaction/{id}', [PaymentController::class, 'getTransaction']);
});

// Only admin can access
Route::middleware('auth:sanctum', 'CheckRole:admin')->group(function () {
    // Template Management Routes
    Route::post('/admin/templates/create', [TemplateController::class, 'create']);
    Route::patch('/admin/templates/patch/{cv_unique_id}', [TemplateController::class, 'patch']);
    Route::delete('/admin/templates/delete/{cv_unique_id}', [TemplateController::class, 'delete']);

    // User Management Routes
    Route::delete('/admin/user/delete/{id}', [UserController::class, 'deleteAccount']);

    // Payment Management Routes
    Route::get('/admin/transactions/get/all-transactions', [PaymentController::class, 'getAllTransactions']);
    Route::get('/admin/transactions/get/{id}', [PaymentController::class, 'getTransaction']);
    Route::patch('/admin/transactions/get/{id}', [PaymentController::class, 'updateTransaction']);
    Route::get('/admin/invoices/get/all-invoices', [PaymentController::class, 'getInvoices']);
    Route::get('/admin/invoices/get/{id}', [PaymentController::class, 'getInvoice']);
});