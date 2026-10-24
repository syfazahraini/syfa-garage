<?php

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/** CRUD kategori (khusus Admin, dijaga middleware "can:manage-master"). */
class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        // Filter yang tidak valid diabaikan, bukan menyebabkan error
        $filters = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
        ])->valid();

        $search = $filters['q'] ?? null;

        $categories = Category::withCount('products')
            ->when($search, fn (Builder $query, string $term) => $query->where('name', 'like', Search::like($term)))
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString(); // filter tetap terbawa saat pindah halaman

        return view('categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('categories.create');
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', compact('category'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        // withTrashed: produk yang sudah "dihapus" (soft delete) tetap ada di database dan masih memakai kategori ini
        if ($category->products()->withTrashed()->exists()) {
            return back()->with('error', 'Kategori ini masih dipakai produk, jadi tidak bisa dihapus.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
