<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Dashboard ringkas. Isinya menyesuaikan role:
 * - Admin : seluruh transaksi
 * - Staff : hanya penjualan miliknya sendiri (kasir; tanpa data pembelian dan daftar belanja ulang)
 * - User  : katalog ringkas (ketersediaan produk, kategori, produk terbaru);
 *           tanpa data transaksi dan tanpa informasi internal (harga beli, stok minimum)
 *
 * Angka dihitung dengan query agregat (COUNT/SUM) di database,
 * bukan dengan mengambil semua baris lalu menjumlahkannya di PHP.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $data = [
            'productCount' => Product::count(),
            'categoryCount' => Category::count(),
        ];

        // User (pelanggan): tampilan katalog seperti "melihat menu"
        if (! $user->can('view-inventory-details')) {
            $data['availableCount'] = Product::where('stock', '>', 0)->count();
            $data['outOfStockCount'] = Product::where('stock', '<=', 0)->count();

            $data['categories'] = Category::withCount([
                'products',
                'products as available_products_count' => fn (Builder $query) => $query->where('stock', '>', 0),
            ])->orderBy('name')->get();

            $data['newestProducts'] = Product::with('category')
                ->latest()
                ->limit(6)
                ->get();
        }

        if ($user->can('view-inventory-details')) {
            $data['lowStockCount'] = Product::lowStock()->count();
        }

        // Daftar "perlu dibeli ulang": urusan gudang/pembelian, hanya untuk Admin
        if ($user->isAdmin()) {
            $data['lowStockProducts'] = Product::with('category')
                ->lowStock()
                ->orderBy('stock')
                ->orderBy('name')
                ->limit(5)
                ->get();
        }

        if ($user->can('view-transactions')) {
            // Admin melihat semua; Staff hanya miliknya (sama seperti aturan di Policy)
            $scope = fn (Builder $query): Builder => $user->isAdmin()
                ? $query
                : $query->where('user_id', $user->id);

            $todayStart = now()->startOfDay();
            $todayEnd = now()->endOfDay();
            $monthStart = now()->startOfMonth();
            $monthEnd = now()->endOfMonth();

            // Hanya transaksi berstatus "completed" yang dihitung sebagai omzet
            $completedSales = fn (): Builder => $scope(Sale::query())
                ->where('status', Sale::STATUS_COMPLETED);

            $data['salesToday'] = $completedSales()
                ->whereBetween('sold_at', [$todayStart, $todayEnd])
                ->selectRaw('COUNT(*) AS trx, COALESCE(SUM(total), 0) AS amount')
                ->first();

            $data['salesMonth'] = $completedSales()
                ->whereBetween('sold_at', [$monthStart, $monthEnd])
                ->selectRaw('COUNT(*) AS trx, COALESCE(SUM(total), 0) AS amount')
                ->first();

            $data['salesCount'] = $completedSales()->count();

            // Ringkasan pembelian hanya untuk Admin (Staff berperan sebagai kasir)
            if ($user->isAdmin()) {
                $data['purchaseMonth'] = Purchase::query()
                    ->where('status', Purchase::STATUS_COMPLETED)
                    ->whereBetween('purchased_at', [$monthStart, $monthEnd])
                    ->selectRaw('COUNT(*) AS trx, COALESCE(SUM(total), 0) AS amount')
                    ->first();

                $data['purchaseCount'] = Purchase::query()
                    ->where('status', Purchase::STATUS_COMPLETED)
                    ->count();
            }

            $data['latestSales'] = $scope(Sale::with('user'))
                ->latest('sold_at')
                ->limit(5)
                ->get();

            $data['isScoped'] = ! $user->isAdmin();
        }

        return view('dashboard', $data);
    }
}
