<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use App\Support\Search;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/** CRUD pemasok (khusus Admin, dijaga middleware "can:manage-master"). */
class SupplierController extends Controller
{
    public function index(Request $request): View
    {
        $filters = Validator::make($request->query(), [
            'q' => ['nullable', 'string', 'max:100'],
        ])->valid();

        $search = $filters['q'] ?? null;

        $suppliers = Supplier::query()
            ->when($search, function (Builder $query, string $term) {
                $like = Search::like($term);

                $query->where(function (Builder $q) use ($like) {
                    $q->where('name', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('email', 'like', $like);
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers', 'search'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil ditambahkan.');
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Data pemasok berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        // Pemasok yang sudah punya riwayat pembelian tidak boleh dihapus
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 'Pemasok ini sudah punya riwayat pembelian sehingga tidak bisa dihapus.');
        }

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Pemasok berhasil dihapus.');
    }
}
