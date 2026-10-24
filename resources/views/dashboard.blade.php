@extends('layouts.app')

@section('title', 'Dashboard')

@php
    $rupiah = fn ($value): string => 'Rp ' . number_format((float) $value, 0, ',', '.');
@endphp

@section('content')
    <div class="card welcome">
        <h2>Halo, {{ auth()->user()->name }}!</h2>
        <p>
            Kamu masuk sebagai
            <span class="badge badge-{{ auth()->user()->role }}">{{ ucfirst(auth()->user()->role) }}</span>.
            Selamat datang di sistem inventory &amp; penjualan {{ config('app.name') }}.
            @isset($isScoped)
                @if ($isScoped)
                    Angka transaksi di bawah hanya menghitung transaksi milikmu.
                @endif
            @endisset
        </p>
    </div>

    {{-- Ringkasan produk: tampil untuk semua role --}}
    <div class="stats">
        <div class="stat">
            <span class="stat-label">Total produk</span>
            <span class="stat-value">{{ $productCount }}</span>
        </div>
        <div class="stat">
            <span class="stat-label">Total kategori</span>
            <span class="stat-value">{{ $categoryCount }}</span>
        </div>
        @isset($availableCount)
            <div class="stat">
                <span class="stat-label">Produk tersedia</span>
                <span class="stat-value">{{ $availableCount }}</span>
            </div>
            <div class="stat {{ $outOfStockCount > 0 ? 'stat-warning' : '' }}">
                <span class="stat-label">Produk habis</span>
                <span class="stat-value">{{ $outOfStockCount }}</span>
            </div>
        @endisset
        @isset($lowStockCount)
            <div class="stat {{ $lowStockCount > 0 ? 'stat-warning' : '' }}">
                <span class="stat-label">Produk stok rendah</span>
                <span class="stat-value">{{ $lowStockCount }}</span>
            </div>
        @endisset
    </div>

    {{-- Katalog untuk User (pelanggan): cari produk, kategori, produk terbaru --}}
    @isset($categories)
        <div class="card" style="margin-top:16px">
            <h2 class="detail-title">Cari produk</h2>
            <form method="GET" action="{{ route('products.index') }}" class="filters">
                <div class="field">
                    <label for="dash-q">SKU atau nama produk</label>
                    <input type="text" id="dash-q" name="q" placeholder="Contoh: oli atau busi" maxlength="100">
                </div>
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Lihat semua produk</a>
            </form>
        </div>

        <div class="card" style="margin-top:16px">
            <h2 class="detail-title">Kategori produk</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="text-right">Jumlah produk</th>
                            <th class="text-right">Tersedia</th>
                            <th class="col-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                            <tr>
                                <td>{{ $category->name }}</td>
                                <td class="text-right">{{ $category->products_count }}</td>
                                <td class="text-right">{{ $category->available_products_count }}</td>
                                <td class="col-actions">
                                    <a href="{{ route('products.index', ['category_id' => $category->id]) }}"
                                       class="btn btn-small btn-secondary">Lihat produk</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty">Belum ada kategori.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card" style="margin-top:16px">
            <h2 class="detail-title">Produk terbaru</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Kategori</th>
                            <th class="text-right">Harga</th>
                            <th class="text-right">Stok</th>
                            <th class="col-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($newestProducts as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td>{{ $product->category->name }}</td>
                                <td class="text-right">{{ $rupiah($product->selling_price) }}</td>
                                <td class="text-right">
                                    @if ($product->stock > 0)
                                        <span class="badge badge-ok">{{ $product->stock }}</span>
                                    @else
                                        <span class="badge badge-low">Habis</span>
                                    @endif
                                </td>
                                <td class="col-actions">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-small btn-secondary">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="empty">Belum ada produk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endisset

    {{-- Ringkasan transaksi: hanya Admin dan Staff --}}
    @isset($salesToday)
        <div class="stats">
            <div class="stat">
                <span class="stat-label">Penjualan hari ini ({{ $salesToday->trx }} transaksi)</span>
                <span class="stat-value stat-value-money">{{ $rupiah($salesToday->amount) }}</span>
            </div>
            <div class="stat">
                <span class="stat-label">Penjualan bulan ini ({{ $salesMonth->trx }} transaksi)</span>
                <span class="stat-value stat-value-money">{{ $rupiah($salesMonth->amount) }}</span>
            </div>
            @isset($purchaseMonth)
                <div class="stat">
                    <span class="stat-label">Pembelian bulan ini ({{ $purchaseMonth->trx }} transaksi)</span>
                    <span class="stat-value stat-value-money">{{ $rupiah($purchaseMonth->amount) }}</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Total transaksi selesai</span>
                    <span class="stat-value">{{ $salesCount + $purchaseCount }}</span>
                    <span class="muted">{{ $salesCount }} penjualan, {{ $purchaseCount }} pembelian</span>
                </div>
            @else
                <div class="stat">
                    <span class="stat-label">Total penjualan selesai</span>
                    <span class="stat-value">{{ $salesCount }}</span>
                </div>
            @endisset
        </div>

        <div class="card" style="margin-top:16px">
            <h2 class="detail-title">Penjualan terbaru</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No. transaksi</th>
                            <th>Tanggal</th>
                            <th>Kasir</th>
                            <th class="text-right">Total</th>
                            <th>Status</th>
                            <th class="col-actions">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($latestSales as $sale)
                            <tr>
                                <td>{{ $sale->sale_number }}</td>
                                <td>{{ $sale->sold_at->format('d M Y H:i') }}</td>
                                <td>{{ $sale->user->name }}</td>
                                <td class="text-right">{{ $rupiah($sale->total) }}</td>
                                <td>
                                    <span class="badge badge-{{ $sale->status }}">
                                        {{ $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                                    </span>
                                </td>
                                <td class="col-actions">
                                    <a href="{{ route('sales.show', $sale) }}" class="btn btn-small btn-secondary">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="empty">Belum ada penjualan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endisset

    {{-- Daftar belanja ulang: hanya Admin --}}
    @isset($lowStockProducts)
        @if ($lowStockProducts->isNotEmpty())
            <div class="card" style="margin-top:16px">
                <h2 class="detail-title">Perlu dibeli ulang</h2>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>SKU</th>
                                <th>Nama</th>
                                <th>Kategori</th>
                                <th class="text-right">Stok</th>
                                <th class="text-right">Minimum</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lowStockProducts as $product)
                                <tr>
                                    <td>{{ $product->sku }}</td>
                                    <td>{{ $product->name }}</td>
                                    <td>{{ $product->category->name }}</td>
                                    <td class="text-right"><span class="badge badge-low">{{ $product->stock }}</span></td>
                                    <td class="text-right">{{ $product->minimum_stock }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="form-actions">
                    <a href="{{ route('products.low-stock') }}" class="btn btn-secondary">Lihat semua stok rendah</a>
                </div>
            </div>
        @endif
    @endisset
@endsection
