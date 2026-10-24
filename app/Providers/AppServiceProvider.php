<?php

namespace App\Providers;

use App\Models\Purchase;
use App\Models\Sale;
use App\Models\User;
use App\Policies\PurchasePolicy;
use App\Policies\SalePolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Waktu transaksi memakai zona waktu Indonesia Barat (WIB)
        config(['app.timezone' => 'Asia/Jakarta']);
        date_default_timezone_set('Asia/Jakarta');

        // Tampilan pagination sederhana (HTML biasa, tanpa Tailwind)
        Paginator::defaultView('pagination::default');
        Paginator::defaultSimpleView('pagination::simple-default');

        /*
         * AUTHORIZATION (dijaga di server, bukan hanya menyembunyikan tombol)
         * Dipakai lewat middleware "can:nama-gate" di routes dan @can di Blade.
         */
        $isAdmin = fn (User $user): bool => $user->role === User::ROLE_ADMIN;
        $isAdminOrStaff = fn (User $user): bool => in_array($user->role, [User::ROLE_ADMIN, User::ROLE_STAFF], true);
        $isAnyRole = fn (User $user): bool => in_array($user->role, [User::ROLE_ADMIN, User::ROLE_STAFF, User::ROLE_USER], true);
        $isStaff = fn (User $user): bool => $user->role === User::ROLE_STAFF;

        // Hanya Admin
        Gate::define('manage-users', $isAdmin);          // kelola pengguna & role
        Gate::define('manage-master', $isAdmin);         // tambah/ubah/hapus kategori, pemasok, produk
        Gate::define('cancel-transactions', $isAdmin);   // batalkan transaksi
        Gate::define('view-reports', $isAdmin);          // laporan lengkap

        // Semua role (Admin, Staff, User): melihat katalog produk dan stok
        Gate::define('view-products', $isAnyRole);           // lihat daftar & detail produk

        // Admin dan Staff saja: informasi internal (harga beli, stok minimum, daftar stok rendah)
        Gate::define('view-inventory-details', $isAdminOrStaff);

        // Admin dan Staff (melihat transaksi)
        Gate::define('view-transactions', $isAdminOrStaff);  // lihat daftar & detail transaksi

        // Hanya Staff: membuat transaksi (sesuai brief: Staff membuat pembelian & penjualan)
        Gate::define('manage-purchases', $isStaff);          // buat transaksi pembelian
        Gate::define('manage-sales', $isStaff);              // buat transaksi penjualan

        // Policy: aturan akses per data (siapa boleh melihat pembelian tertentu)
        Gate::policy(Purchase::class, PurchasePolicy::class);
        Gate::policy(Sale::class, SalePolicy::class);
    }
}
