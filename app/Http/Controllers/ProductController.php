<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Produk.
 * - index, show, lowStock : Admin dan Staff (gate "view-products")
 * - create, store, edit, update, destroy : khusus Admin (gate "manage-master")
 * Aturan ini dipasang di routes/web.php.
 */
class ProductController extends Controller
{
    public function index(Request $request): View
    {
        // Filter yang tidak valid diabaikan, bukan menyebabkan error
        $filters = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
        ])->valid();

        $search = $filters['q'] ?? null;
        $categoryId = $filters['category_id'] ?? null;

        $products = Product::with('category') // eager loading: hindari query berulang (N+1)
            ->when($search, function (Builder $query, string $term) {
                $like = Search::like($term);

                $query->where(function (Builder $q) use ($like) {
                    $q->where('sku', 'like', $like)->orWhere('name', 'like', $like);
                });
            })
            ->when($categoryId, fn (Builder $query, $id) => $query->where('category_id', $id))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // filter tetap terbawa saat pindah halaman

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'search', 'categoryId'));
    }

    public function lowStock(): View
    {
        $products = Product::with('category')
            ->lowStock()
            ->orderBy('stock')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('products.low-stock', compact('products'));
    }

    public function show(Product $product): View
    {
        $product->load('category');

        return view('products.show', compact('product'));
    }

    public function create(): View
    {
        return view('products.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        // Stok awal selalu 0. Stok bertambah lewat transaksi pembelian.
        $product = Product::create($request->validated() + ['stock' => 0]);

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('products.edit', [
            'product' => $product,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        // Stok tidak ikut diubah di sini
        $product->update($request->validated());

        return redirect()->route('products.show', $product)->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Soft delete: data produk tetap tersimpan di database, sehingga riwayat transaksi lama aman.
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
