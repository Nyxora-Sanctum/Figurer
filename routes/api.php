<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TransactionController;
use App\Http\Controllers\API\AIController;
use App\Http\Controllers\API\AccountController;
use App\Http\Controllers\API\TemplateController;

// Public routes to access login and register
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post(('/auth/login'), [AuthController::class,'login'])->name('login');

// Both user and admin can access
Route::middleware(['auth:sanctum'])->group(function () {
    // Template Routes
    Route::get('/templates/get/all-templates', [TemplateController::class, 'getAllTemplates']);
    Route::get('/templates/inventory/get/{id}', [TemplateController::class, 'getOwnedByID']);
    Route::get('/templates/get/{id}', [TemplateController::class, 'getByID']);
    Route::get('/templates/inventory/all-used', [TemplateController::class, 'getAllUsed']);

    // Logout Routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // AI Routes
    Route::post('/ai/prompt', [AIController::class, 'AIOutput']);
    
    // User Auth Check routes
    Route::get('/auth/check', [AuthController::class, 'checkAuth']);
});

// Only user can access
Route::middleware('auth:sanctum', 'CheckRole:user')->group(function () {
    // User Management routes
    Route::get('/user/profile', [AccountController::class, 'getCurrentProfile']);
    Route::patch('/user/profile', [AccountController::class, 'updateProfile']);

    // Template Management routes
    Route::get('/templates/inventory', [TemplateController::class, 'getAllOwned']);
    Route::post('/templates/use/{id}', [TemplateController::class,'useTemplate']);

    // Payment routes
    Route::post('/transaction/buy', [TransactionController::class,'payment']);
    Route::get('/transaction/{id}', [TransactionController::class, 'getTransaction']);
    Route::get('/invoices/get/{id}', [TransactionController::class, 'getinvoicebyid']);
    Route::get('/invoices/get', [TransactionController::class, 'getAllInvoicesByAccountID']);
});

// Only admin can access
Route::middleware('auth:sanctum', 'CheckRole:admin')->group(function () {
    // Template Management Routes
    Route::post('/admin/templates/create', [TemplateController::class, 'create']);
    Route::patch('/admin/templates/patch/{cv_unique_id}', [TemplateController::class, 'patch']);
    Route::delete('/admin/templates/delete/{cv_unique_id}', [TemplateController::class, 'delete']);
    Route::get('/admin/templates/get/{id}', [TemplateController::class, 'getInventoryByID']);
    Route::delete('/admin/templates/delete/{id}/{cv_unique_id}', [TemplateController::class, 'deleteTemplateInventory']);

    // User Management Routes
    Route::patch('/admin/accounts/update/{id}', [AccountController::class, 'updateProfileAdmin']);
    Route::delete('/admin/accounts/delete/{id}', [AccountController::class, 'deleteAccount']);
    Route::get('/admin/data/accounts/get', [AccountController::class, 'getAllAccounts']);
    Route::get('/admin/data/accounts/get/{id}', [AccountController::class, 'getAccountById']);
    Route::get('/admin/data/accounts/get/latest/{count}', [AccountController::class, 'getNewUsers']);

    //  Transactions Management Routes
    Route::get('/admin/transactions/get/all-transactions', [TransactionController::class, 'getAllTransactions']);
    Route::get('/admin/transactions/get/{id}', [TransactionController::class, 'getTransaction']);
    Route::get('/admin/data/transactions/get/latest/{id}', [TransactionController::class, 'getNewTransactions']);
    Route::patch('/admin/transactions/get/{id}', [TransactionController::class, 'updateTransaction']);
    Route::post('/admin/transactions/complete/{id}', [TransactionController::class, 'completeTransactionByOrderID']);
    Route::post('/admin/transactions/decline/{id}', [TransactionController::class, 'declineTransactionByOrderID']);

    //Invoice Management Routes
    Route::get('/admin/invoices/get/all-invoices', [TransactionController::class, 'getInvoices']);
    Route::get('/admin/invoices/get/{id}', [TransactionController::class, 'getinvoicebyid']);

    // AI Configuration Routes
    Route::get('/admin/config', [AIController::class, 'getAIConfig']);
    Route::patch('/admin/config', [AIController::class, 'updateAIConfig']);

    // Data Routes
    Route::get('/admin/data/get/total-users', [AccountController::class, 'getTotalUsers']);
    Route::get('/admin/data/get/total-incomes', [TransactionController::class, 'getTotalIncomes']);
    Route::get('/admin/data/get/total-orders', [TransactionController::class, 'getTotalOrders']);
    Route::get('/admin/data/get/total-templates', [TemplateController::class, 'getTotalTemplates']);
});