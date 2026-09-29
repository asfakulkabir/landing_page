<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DeliveryZoneController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');
Route::get('/thank-you/{orderNumber}', [CheckoutController::class, 'thankYou'])->name('thank-you');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
    });

    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('products', ProductController::class)->except('show');
        Route::post('products/{product}/toggle', [ProductController::class, 'toggle'])->name('products.toggle');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

        Route::resource('delivery-zones', DeliveryZoneController::class)->except('show');
        Route::post('delivery-zones/reorder', [DeliveryZoneController::class, 'reorder'])->name('delivery-zones.reorder');

        Route::resource('testimonials', TestimonialController::class)->except('show');
        Route::post('testimonials/reorder', [TestimonialController::class, 'reorder'])->name('testimonials.reorder');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });
});
