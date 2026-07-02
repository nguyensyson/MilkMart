<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlaceholderController as AdminPlaceholderController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AdminRegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/cart', [CartController::class, 'show'])->name('cart.show');
Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index')->middleware('auth');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    // No {token} URI segment on purpose — see NewPasswordController::create().
    Route::get('/reset-password', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.update');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});

// Backoffice shell (Admin + Staff): dashboard plus the modules docs mark as
// shared (Orders, Suppliers). Admin-only modules are nested below behind
// the stricter 'admin' middleware.
Route::middleware(['auth', 'backoffice'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/orders', [AdminPlaceholderController::class, 'orders'])->name('orders.index');
    Route::get('/suppliers', [AdminPlaceholderController::class, 'suppliers'])->name('suppliers.index');

    Route::middleware('admin')->group(function () {
        Route::get('/register', [AdminRegisteredUserController::class, 'create'])->name('register');
        Route::post('/register', [AdminRegisteredUserController::class, 'store']);

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::put('/users/{user}/status', [AdminUserController::class, 'updateStatus'])->name('users.status');

        Route::get('/products', [AdminPlaceholderController::class, 'products'])->name('products.index');
        Route::get('/categories', [AdminPlaceholderController::class, 'categories'])->name('categories.index');
        Route::get('/brands', [AdminPlaceholderController::class, 'brands'])->name('brands.index');
        Route::get('/vouchers', [AdminPlaceholderController::class, 'vouchers'])->name('vouchers.index');
        Route::get('/reports', [AdminPlaceholderController::class, 'reports'])->name('reports.index');
    });
});
