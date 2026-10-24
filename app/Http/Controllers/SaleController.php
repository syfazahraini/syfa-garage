<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Requests\StoreSaleRequest;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Penjualan (barang keluar).
 * Controller hanya mengurus request dan response; logika stok ada di SaleService.
 */
class SaleController extends Controller
{
    public function __construct(private readonly SaleService $service)
    {
    }

    public function index(Request $request): View
    {
        // Filter yang tidak valid diabaikan, bukan menyebabkan error
        $filters = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([Sale::STATUS_COMPLETED, Sale::STATUS_CANCELLED])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ])->valid();

        $search = $filters['q'] ?? null;
        $status = $filters['status'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $user = $request->user();

        $sales = Sale::with('user') // eager loading: hindari query berulang (N+1)
            // Admin melihat semua. Staff hanya melihat penjualan buatannya sendiri.
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where('user_id', $user->id))
            ->when($search, function (Builder $query, string $term) {
                $like = Search::like($term);

                $query->where(function (Builder $q) use ($like) {
                    $q->where('sale_number', 'like', $like)
                        ->orWhereHas('user', fn (Builder $u) => $u->where('name', 'like', $like));
                });
            })
            ->when($status, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($dateFrom, fn (Builder $query, string $value) => $query->where('sold_at', '>=', $value . ' 00:00:00'))
            ->when($dateTo, fn (Builder $query, string $value) => $query->where('sold_at', '<=', $value . ' 23:59:59'))
            ->orderByDesc('sold_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString(); // filter tetap terbawa saat pindah halaman

        return view('sales.index', compact('sales', 'search', 'status', 'dateFrom', 'dateTo'));
    }

    public function create(): View
    {
        return view('sales.create', [
            // Hanya produk yang stoknya masih ada
            'products' => Product::where('stock', '>', 0)
                ->orderBy('name')
                ->get(['id', 'sku', 'name', 'selling_price', 'stock']),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        try {
            $sale = $this->service->create($request->user(), $request->validated());
        } catch (BusinessRuleException $e) {
            // Aturan bisnis dilanggar (misal stok kurang): tolak, data dan stok tidak berubah, input dipertahankan
            return back()->withInput()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            // Kegagalan database/server: sudah di-rollback. Detail teknis hanya ke log, tidak ke user.
            report($e);

            return back()->withInput()->with(
                'error',
                'Terjadi kesalahan pada server. Transaksi dibatalkan dan stok tidak berubah. Silakan coba lagi.'
            );
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', "Penjualan {$sale->sale_number} berhasil disimpan dan stok sudah berkurang.");
    }

    public function show(Sale $sale): View
    {
        // Policy: admin boleh semua, staff hanya miliknya sendiri (selain itu 403)
        Gate::authorize('view', $sale);

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale): RedirectResponse
    {
        try {
            $this->service->cancel($sale);
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Terjadi kesalahan pada server. Pembatalan gagal dan stok tidak berubah.');
        }

        return redirect()
            ->route('sales.show', $sale)
            ->with('success', 'Penjualan dibatalkan dan stok sudah dikembalikan.');
    }
}
