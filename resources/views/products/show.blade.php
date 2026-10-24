@extends('layouts.app')

@section('title', 'Detail Produk')

@section('actions')
    @can('manage-master')
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">Ubah produk</a>
    @endcan
@endsection

@section('content')
    <div class="card card-form">
        <h2 class="detail-title">{{ $product->name }}</h2>

        <dl class="detail-list">
            <dt>SKU</dt>
            <dd>{{ $product->sku }}</dd>

            <dt>Kategori</dt>
            <dd>{{ $product->category->name }}</dd>

            @can('view-inventory-details')
                <dt>Harga beli</dt>
                <dd>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</dd>
            @endcan

            <dt>Harga jual</dt>
            <dd>Rp {{ number_format($product->selling_price, 0, ',', '.') }}</dd>

            <dt>Stok</dt>
            <dd>
                <span class="badge {{ $product->isLowStock() ? 'badge-low' : 'badge-ok' }}">{{ $product->stock }}</span>
                @if ($product->isLowStock())
                    <span class="muted">(stok rendah)</span>
                @endif
            </dd>

            @can('view-inventory-details')
                <dt>Stok minimum</dt>
                <dd>{{ $product->minimum_stock }}</dd>
            @endcan
        </dl>

        <div class="form-actions">
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali ke daftar</a>
        </div>
    </div>
@endsection
