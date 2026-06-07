<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\DashboardController;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Public product and category viewing
Route::apiResource('categories', CategoryController::class)->only(['index', 'show']);
Route::apiResource('products', ProductController::class)->only(['index', 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    
    // Orders
    Route::get('/my-orders', [OrderController::class, 'myOrders']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    
    // Admin only routes
    Route::middleware('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
        
        // Categories
        Route::apiResource('categories', CategoryController::class)->except(['index', 'show']);
        
        // Products
        Route::apiResource('products', ProductController::class)->except(['index', 'show']);
        Route::put('/products/{product}/stock', [ProductController::class, 'updateStock']);
        
        // Orders management
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        
        // Users management
        Route::get('/customers', [AuthController::class, 'getCustomers']);
        Route::get('/customers/{id}', [AuthController::class, 'getCustomer']);
        Route::put('/customers/{id}', [AuthController::class, 'updateCustomer']);
        Route::put('/customers/{user}/status', [AuthController::class, 'updateCustomerStatus']);
        Route::delete('/customers/{user}', [AuthController::class, 'deleteCustomer']);

    });
});


