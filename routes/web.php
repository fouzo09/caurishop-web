<?php

use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\InstallmentController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'authenticate'])->name('login.authenticate');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::prefix('admin')->name('admin.')->group(function () {

        Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}', [UserController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/suspend', [UserController::class, 'suspend'])->name('suspend');
            Route::post('/{user}/activate', [UserController::class, 'activate'])->name('activate');
            Route::post('/{user}/verify', [UserController::class, 'verify'])->name('verify');
        });

        Route::prefix('companies')->name('companies.')->group(function () {
            Route::get('/', [CompanyController::class, 'index'])->name('index');
            Route::get('/create', [CompanyController::class, 'create'])->name('create');
            Route::post('/', [CompanyController::class, 'store'])->name('store');
            Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
            Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
            Route::put('/{company}', [CompanyController::class, 'update'])->name('update');
            Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
            Route::post('/{company}/activate', [CompanyController::class, 'activate'])->name('activate');
            Route::post('/{company}/deactivate', [CompanyController::class, 'deactivate'])->name('deactivate');
        });

        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/create', [CustomerController::class, 'create'])->name('create');
            Route::post('/', [CustomerController::class, 'store'])->name('store');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{customer}/edit', [CustomerController::class, 'edit'])->name('edit');
            Route::put('/{customer}', [CustomerController::class, 'update'])->name('update');
            Route::delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
            Route::post('/{customer}/activate', [CustomerController::class, 'activate'])->name('activate');
            Route::post('/{customer}/deactivate', [CustomerController::class, 'deactivate'])->name('deactivate');
        });

        Route::prefix('products')->name('products.')->group(function () {
            Route::get('/', [ProductController::class, 'index'])->name('index');
            Route::get('/create', [ProductController::class, 'create'])->name('create');
            Route::post('/', [ProductController::class, 'store'])->name('store');
            Route::get('/{product}', [ProductController::class, 'show'])->name('show');
            Route::get('/{product}/edit', [ProductController::class, 'edit'])->name('edit');
            Route::put('/{product}', [ProductController::class, 'update'])->name('update');
            Route::delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
            Route::post('/{product}/activate', [ProductController::class, 'activate'])->name('activate');
            Route::post('/{product}/deactivate', [ProductController::class, 'deactivate'])->name('deactivate');
            Route::post('/{product}/publish', [ProductController::class, 'publish'])->name('publish');
            Route::post('/{product}/unpublish', [ProductController::class, 'unpublish'])->name('unpublish');

            // Variants
            Route::get('/{product}/variants/create', [ProductController::class, 'createVariant'])->name('variants.create');
            Route::post('/{product}/variants', [ProductController::class, 'storeVariant'])->name('variants.store');
            Route::get('/{product}/variants/{variant}/edit', [ProductController::class, 'editVariant'])->name('variants.edit');
            Route::put('/{product}/variants/{variant}', [ProductController::class, 'updateVariant'])->name('variants.update');
            Route::delete('/{product}/variants/{variant}', [ProductController::class, 'destroyVariant'])->name('variants.destroy');
        });

        Route::prefix('installments')->name('installments.')->group(function () {
            Route::get('/', [InstallmentController::class, 'index'])->name('index');
            Route::get('/{installment}/pay', [InstallmentController::class, 'pay'])->name('pay');
            Route::post('/{installment}/pay', [InstallmentController::class, 'recordPayment'])->name('record-payment');
        });

        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [PaymentController::class, 'index'])->name('index');
        });

        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/', [OrderController::class, 'index'])->name('index');
            Route::get('/create', [OrderController::class, 'create'])->name('create');
            Route::post('/', [OrderController::class, 'store'])->name('store');
            Route::get('/{order}', [OrderController::class, 'show'])->name('show');
            Route::post('/{order}/confirm', [OrderController::class, 'confirm'])->name('confirm');
            Route::post('/{order}/deliver', [OrderController::class, 'deliver'])->name('deliver');
            Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        });
    });
});
