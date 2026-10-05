<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::resource('customers', CustomerController::class)->except('show');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
    Route::middleware('admin')->group(function () {
        Route::resource('products', ProductController::class)->except(['show', 'index']);
        Route::resource('categories', CategoryController::class)->except('show');
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::get('/business', [BusinessController::class, 'edit'])->name('business.edit');
        Route::put('/business', [BusinessController::class, 'update'])->name('business.update');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    });
});
