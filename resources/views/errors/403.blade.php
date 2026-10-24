<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Akses ditolak</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="guest-body">
    <div class="guest-card error-card">
        <div class="error-code">403</div>
        <h1>Akses ditolak</h1>
        <p>Kamu tidak punya izin untuk membuka halaman ini.</p>
        <a href="{{ url('/dashboard') }}" class="btn btn-primary">Kembali ke dashboard</a>
    </div>
</body>
</html>
