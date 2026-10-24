@extends('layouts.app')

@section('title', 'Produk')

@section('actions')
    @can('manage-master')
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah produk</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('products.index') }}" class="filters">
            <div class="field">
                <label for="q">Cari SKU / nama</label>
                <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Contoh: OLI atau busi" maxlength="100">
            </div>

            <div class="field">
                <label for="category_id">Kategori</label>
                <select id="category_id" name="category_id">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($search || $categoryId)
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th class="text-right">Harga jual</th>
                        <th class="text-right">Stok</th>
                        <th class="col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td>{{ $product->sku }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name }}</td>
                            <td class="text-right">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                            <td class="text-right">
                                <span class="badge {{ $product->isLowStock() ? 'badge-low' : 'badge-ok' }}">{{ $product->stock }}</span>
                            </td>
                            <td class="col-actions">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-small btn-secondary">Detail</a>

                                @can('manage-master')
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-small btn-secondary">Ubah</a>

                                    <form method="POST" action="{{ route('products.destroy', $product) }}" class="inline-form"
                                          onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="empty">Produk tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">{{ $products->links() }}</div>
    </div>
@endsection
