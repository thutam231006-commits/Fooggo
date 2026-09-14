<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FoodController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('foods', [FoodController::class, 'index']);
    Route::get('foods/{food}', [FoodController::class, 'show']);
    Route::get('foods/{food}/reviews', [ReviewController::class, 'index']);
    Route::middleware('api.token')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::middleware('api.token:customer')->group(function () {
            Route::get('cart', [CartController::class, 'show']);
            Route::post('cart/items', [CartController::class, 'store']);
            Route::patch('cart/items/{cartItem}', [CartController::class, 'update']);
            Route::delete('cart/items/{cartItem}', [CartController::class, 'destroy']);
            Route::delete('cart', [CartController::class, 'clear']);
            Route::post('foods/{food}/reviews', [ReviewController::class, 'store']);
            Route::post('orders', [OrderController::class, 'store']);
            Route::patch('orders/{order}', [OrderController::class, 'update']);
            Route::delete('orders/{order}', [OrderController::class, 'destroy']);
            Route::post('orders/{order}/payment', [PaymentController::class, 'store']);
        });
        Route::get('orders', [OrderController::class, 'index']);
        Route::get('orders/{order}', [OrderController::class, 'show']);
        Route::patch('orders/{order}/status', [OrderController::class, 'status'])->middleware('api.token:staff,admin');
        Route::apiResource('foods', FoodController::class)->except(['index', 'show'])->middleware('api.token:admin');
        Route::apiResource('users', UserController::class)->middleware('api.token:admin');
        Route::get('reports/revenue', [ReportController::class, 'revenue'])->middleware('api.token:admin');
        Route::get('reports/best-sellers', [ReportController::class, 'bestSellers'])->middleware('api.token:admin');
    });
});
