<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GudangController;
use App\Http\Controllers\KasirController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Toko Retail ALFA
|--------------------------------------------------------------------------
*/

// Root redirect
Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication Routes (Guest)
Route::controller(AuthController::class)->group(function () {
    Route::get('/login', 'showLogin')->name('login');
    Route::post('/login', 'login')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

// 1. UKasir Routes (Desktop POS Mode)
Route::middleware(['auth', 'role:UKasir'])->prefix('kasir')->name('kasir.')->group(function () {
    Route::get('/', [KasirController::class, 'index'])->name('pos');
    Route::post('/find-product', [KasirController::class, 'findProduct'])->name('find-product');
    Route::post('/checkout', [KasirController::class, 'checkout'])->name('checkout');
    Route::get('/receipt/{id}', [KasirController::class, 'printReceipt'])->name('receipt');
    Route::get('/history', [KasirController::class, 'history'])->name('history');
});

// 2. UGudang Routes (CRUD Master Data Barang & Manajemen Stok)
Route::middleware(['auth', 'role:UGudang'])->prefix('gudang')->name('gudang.')->group(function () {
    Route::get('/', [GudangController::class, 'index'])->name('index');
    Route::post('/product', [GudangController::class, 'storeProduct'])->name('product.store');
    Route::put('/product/{id}', [GudangController::class, 'updateProduct'])->name('product.update');
    Route::delete('/product/{id}', [GudangController::class, 'destroyProduct'])->name('product.destroy');
    Route::post('/stock-in', [GudangController::class, 'stockIn'])->name('stock.in');
    Route::post('/stock-adjustment', [GudangController::class, 'stockAdjustment'])->name('stock.adjustment');
    Route::get('/logs', [GudangController::class, 'stockLogs'])->name('logs');
});

// 3. UAdmin Routes (Mobile-First Responsive Dashboard - Read Only)
Route::middleware(['auth', 'role:UAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/low-stock', [AdminController::class, 'lowStock'])->name('low-stock');
    Route::get('/sale/{id}', [AdminController::class, 'saleDetail'])->name('sale.detail');
});
