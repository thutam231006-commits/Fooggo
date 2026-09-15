<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\WebAdminFoodController;
use App\Http\Controllers\WebAdminReportController;
use App\Http\Controllers\WebAdminUserController;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\WebCartController;
use App\Http\Controllers\WebController;
use App\Http\Controllers\WebOrderController;
use App\Http\Controllers\WebReviewController;
use App\Http\Controllers\WebStaffOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [WebController::class, 'home'])->name('home');
Route::get('/foods/{food}', [WebController::class, 'show'])->name('foods.show');
Route::get('/pickup/{order}', [WebOrderController::class, 'verifyPickup'])->middleware('signed')->name('pickup.verify');

Route::middleware('guest')->group(function () {
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register'])->middleware('throttle:10,1');
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

    Route::middleware('role:customer')->group(function () {
        Route::get('/customer/dashboard', [DashboardController::class, 'customer'])->name('customer.dashboard');
        Route::get('/cart', [WebCartController::class, 'show'])->name('cart.show');
        Route::post('/cart/items', [WebCartController::class, 'store'])->name('cart.items.store');
        Route::patch('/cart/items/{cartItem}', [WebCartController::class, 'update'])->name('cart.items.update');
        Route::delete('/cart/items/{cartItem}', [WebCartController::class, 'destroy'])->name('cart.items.destroy');
        Route::post('/orders', [WebOrderController::class, 'store'])->name('web.orders.store');
        Route::get('/orders/{order}', [WebOrderController::class, 'show'])->name('web.orders.show');
        Route::post('/orders/{order}/payment', [WebOrderController::class, 'pay'])->name('web.orders.pay');
        Route::delete('/orders/{order}', [WebOrderController::class, 'cancel'])->name('web.orders.cancel');
        Route::post('/orders/{order}/foods/{food}/review', [WebReviewController::class, 'store'])->name('web.reviews.store');
    });

    Route::middleware('role:staff,admin')->group(function () {
        Route::get('/staff/dashboard', [DashboardController::class, 'staff'])->name('staff.dashboard');
        Route::patch('/staff/orders/{order}/status', [WebStaffOrderController::class, 'update'])->name('staff.orders.status');
    });

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');
        Route::resource('foods', WebAdminFoodController::class)->except(['show']);
        Route::get('users', [WebAdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [WebAdminUserController::class, 'create'])->name('users.create');
        Route::post('users', [WebAdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [WebAdminUserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [WebAdminUserController::class, 'update'])->name('users.update');
        Route::get('reports', [WebAdminReportController::class, 'index'])->name('reports.index');
    });
});
