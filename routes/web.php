<?php

use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\PlaceholderController as AdminPlaceholderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductImageController as AdminProductImageController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Auth\AdminRegisteredUserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ProductController::class, 'index'])->name('home');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
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

        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        // create/edit are not in the API draft but are the Inertia pages
        // needed to reach the documented store/update/variant/image actions.
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{product}', [AdminProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        Route::post('/products/{product}/variants', [AdminProductVariantController::class, 'store'])->name('variants.store');
        Route::put('/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('variants.update');
        Route::delete('/variants/{variant}', [AdminProductVariantController::class, 'destroy'])->name('variants.destroy');

        Route::post('/variants/{variant}/images', [AdminProductImageController::class, 'store'])->name('images.store');
        Route::delete('/images/{image}', [AdminProductImageController::class, 'destroy'])->name('images.destroy');
        Route::put('/images/{image}/primary', [AdminProductImageController::class, 'primary'])->name('images.primary');

        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        Route::get('/brands', [AdminBrandController::class, 'index'])->name('brands.index');
        Route::post('/brands', [AdminBrandController::class, 'store'])->name('brands.store');
        Route::put('/brands/{brand}', [AdminBrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{brand}', [AdminBrandController::class, 'destroy'])->name('brands.destroy');

        Route::get('/vouchers', [AdminPlaceholderController::class, 'vouchers'])->name('vouchers.index');
        Route::get('/reports', [AdminPlaceholderController::class, 'reports'])->name('reports.index');
    });
});
