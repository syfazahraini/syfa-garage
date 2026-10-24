@extends('layouts.app')

@section('title', 'Stok Rendah')

@section('content')
    <div class="card">
        <p class="muted intro">Produk dengan stok sama dengan atau di bawah stok minimum. Segera lakukan pembelian ulang.</p>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th class="text-right">Stok</th>
                        <th class="text-right">Minimum</th>
                        <th class="col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td class="text-right"><span class="badge badge-low">{{ $product->stock }}</span></td>
                            <td class="text-right">{{ $product->minimum_stock }}</td>
                            <td class="col-actions">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-small btn-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Semua stok aman. Tidak ada produk dengan stok rendah.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">{{ $products->links() }}</div>
    </div>
@endsection
