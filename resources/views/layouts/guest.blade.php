<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Masuk') — {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-body">
    <div class="guest-card">
        <div class="guest-brand">
            <div class="guest-logo">SG</div>
            <h1>{{ config('app.name') }}</h1>
            <p>Inventory &amp; Sales Management</p>
        </div>

        @include('partials.flash')

        @yield('content')
    </div>
</body>
</html>
