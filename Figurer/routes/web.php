<?php

use Illuminate\Support\Facades\Route;


// All Can Access
Route::middleware(['auth:sanctum'])->group(function () {

});


// Only user can access
Route::middleware('auth:sanctum', 'CheckRole:user')->group(function () {
    
});


// Only admin can access
Route::middleware('auth:sanctum', 'CheckRole:admin')->group(function () {
    
});