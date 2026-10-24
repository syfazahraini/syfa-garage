<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessRuleException;
use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\PurchaseService;
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
 * Pembelian (barang masuk).
 * Controller hanya mengurus request dan response; logika stok ada di PurchaseService.
 */
class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseService $service)
    {
    }

    public function index(Request $request): View
    {
        // Filter yang tidak valid diabaikan, bukan menyebabkan error
        $filters = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in([Purchase::STATUS_COMPLETED, Purchase::STATUS_CANCELLED])],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d'],
        ])->valid();

        $search = $filters['q'] ?? null;
        $status = $filters['status'] ?? null;
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;

        $user = $request->user();

        $purchases = Purchase::with(['supplier', 'user']) // eager loading: hindari query berulang (N+1)
            // Admin melihat semua. Staff hanya melihat pembelian buatannya sendiri.
            ->when(! $user->isAdmin(), fn (Builder $query) => $query->where('user_id', $user->id))
            ->when($search, function (Builder $query, string $term) {
                $like = Search::like($term);

                $query->where(function (Builder $q) use ($like) {
                    $q->where('purchase_number', 'like', $like)
                        ->orWhereHas('supplier', fn (Builder $s) => $s->where('name', 'like', $like));
                });
            })
            ->when($status, fn (Builder $query, string $value) => $query->where('status', $value))
            ->when($dateFrom, fn (Builder $query, string $value) => $query->where('purchased_at', '>=', $value . ' 00:00:00'))
            ->when($dateTo, fn (Builder $query, string $value) => $query->where('purchased_at', '<=', $value . ' 23:59:59'))
            ->orderByDesc('purchased_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString(); // filter tetap terbawa saat pindah halaman

        return view('purchases.index', compact('purchases', 'search', 'status', 'dateFrom', 'dateTo'));
    }

    public function create(): View
    {
        return view('purchases.create', [
            'suppliers' => Supplier::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(['id', 'sku', 'name', 'purchase_price']),
        ]);
    }

    public function store(StorePurchaseRequest $request): RedirectResponse
    {
        try {
            $purchase = $this->service->create($request->user(), $request->validated());
        } catch (BusinessRuleException $e) {
            // Aturan bisnis dilanggar: tolak, data dan stok tidak berubah, input form dipertahankan
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
            ->route('purchases.show', $purchase)
            ->with('success', "Pembelian {$purchase->purchase_number} berhasil disimpan dan stok sudah bertambah.");
    }

    public function show(Purchase $purchase): View
    {
        // Policy: admin boleh semua, staff hanya miliknya sendiri (selain itu 403)
        Gate::authorize('view', $purchase);

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase): RedirectResponse
    {
        try {
            $this->service->cancel($purchase);
        } catch (BusinessRuleException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Terjadi kesalahan pada server. Pembatalan gagal dan stok tidak berubah.');
        }

        return redirect()
            ->route('purchases.show', $purchase)
            ->with('success', 'Pembelian dibatalkan dan stok sudah dikembalikan.');
    }
}
