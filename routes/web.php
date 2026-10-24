<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Halaman awal selalu diarahkan ke dashboard (kalau belum login, otomatis ke halaman login)
Route::redirect('/', '/dashboard');

// Hanya untuk tamu (belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])
        ->middleware('throttle:10,1') // maksimal 10 percobaan per menit
        ->name('login.attempt');
});

// Semua user yang sudah login (Admin & Staff)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Hanya Admin: kelola pengguna dan role
Route::middleware(['auth', 'can:manage-users'])->group(function () {
    Route::resource('users', UserController::class)->except('show');
});

// Hanya Admin: kelola data master (kategori, pemasok, produk)
// PENTING: route products (create/edit/dst) harus didaftarkan SEBELUM products.show di bawah,
// supaya "/products/create" tidak dianggap sebagai "/products/{product}".
Route::middleware(['auth', 'can:manage-master'])->group(function () {
    Route::resource('categories', CategoryController::class)->except('show');
    Route::resource('suppliers', SupplierController::class)->except('show');
    Route::resource('products', ProductController::class)->except(['index', 'show']);
});

// Admin dan Staff: daftar stok rendah (informasi internal, User/pelanggan tidak boleh).
// Didaftarkan SEBELUM products.show supaya "/products/low-stock" tidak dianggap "/products/{product}".
Route::middleware(['auth', 'can:view-inventory-details'])->group(function () {
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->name('products.low-stock');
});

// Semua role (Admin, Staff, User): boleh melihat data produk dan stok
Route::middleware(['auth', 'can:view-products'])->group(function () {
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');
});

// Hanya Staff: membuat pembelian (barang masuk) dan penjualan (barang keluar).
// PENTING: didaftarkan SEBELUM route show, supaya "/sales/create" tidak dianggap "/sales/{sale}".
Route::middleware(['auth', 'can:manage-purchases'])->group(function () {
    Route::resource('purchases', PurchaseController::class)->only(['create', 'store']);
});
Route::middleware(['auth', 'can:manage-sales'])->group(function () {
    Route::resource('sales', SaleController::class)->only(['create', 'store']);
});

// Admin dan Staff: melihat daftar dan detail transaksi.
// Admin melihat semua; Staff hanya miliknya sendiri (diatur oleh Policy dan filter di controller).
Route::middleware(['auth', 'can:view-transactions'])->group(function () {
    Route::resource('purchases', PurchaseController::class)->only(['index', 'show']);
    Route::resource('sales', SaleController::class)->only(['index', 'show']);
});

// Hanya Admin: membatalkan pembelian (stok dikembalikan)
Route::middleware(['auth', 'can:cancel-transactions'])->group(function () {
    Route::post('/purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
});

// Hanya Admin: membatalkan penjualan (stok dikembalikan)
Route::middleware(['auth', 'can:cancel-transactions'])->group(function () {
    Route::post('/sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
});
