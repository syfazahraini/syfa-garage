<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
@php
    // Menu navigasi. Menu hanya muncul jika route-nya sudah ada DAN user berhak (gate).
    // Catatan: ini hanya tampilan. Keamanan sebenarnya ada di middleware "can:" pada routes.
    $nav = [
        ['route' => 'dashboard',        'label' => 'Dashboard',  'active' => 'dashboard',     'can' => null],
        ['route' => 'products.index',   'label' => 'Produk',     'active' => 'products.*',    'can' => 'view-products', 'except' => 'products.low-stock'],
        ['route' => 'products.low-stock', 'label' => 'Stok Rendah', 'active' => 'products.low-stock', 'can' => 'view-inventory-details'],
        ['route' => 'categories.index', 'label' => 'Kategori',   'active' => 'categories.*',  'can' => 'manage-master'],
        ['route' => 'suppliers.index',  'label' => 'Pemasok',    'active' => 'suppliers.*',   'can' => 'manage-master'],
        ['route' => 'purchases.index',  'label' => 'Pembelian',  'active' => 'purchases.*',   'can' => 'view-transactions'],
        ['route' => 'sales.index',      'label' => 'Penjualan',  'active' => 'sales.*',       'can' => 'view-transactions'],
        ['route' => 'users.index',      'label' => 'Pengguna',   'active' => 'users.*',       'can' => 'manage-users'],
    ];
    $currentUser = auth()->user();
@endphp

<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-logo">SG</div>
            <div>
                <strong>{{ config('app.name') }}</strong>
                <small>Inventory &amp; Sales</small>
            </div>
        </div>

        <nav class="nav">
            @foreach ($nav as $item)
                @continue(! \Illuminate\Support\Facades\Route::has($item['route']))
                @continue($item['can'] && ! $currentUser->can($item['can']))

                @php
                    $isActive = request()->routeIs($item['active'])
                        && ! (isset($item['except']) && request()->routeIs($item['except']));
                @endphp
                <a href="{{ route($item['route']) }}"
                   class="nav-link {{ $isActive ? 'active' : '' }}">
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="sidebar-user">
            <div class="user-name">{{ $currentUser->name }}</div>
            <span class="badge badge-{{ $currentUser->role }}">{{ ucfirst($currentUser->role) }}</span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-light btn-block">Keluar</button>
            </form>
        </div>
    </aside>

    <main class="main">
        <header class="page-header">
            <h1>@yield('title', 'Dashboard')</h1>
            @yield('actions')
        </header>

        @include('partials.flash')

        @yield('content')
    </main>
</div>

@stack('scripts')
</body>
</html>
