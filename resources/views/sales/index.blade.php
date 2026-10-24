@extends('layouts.app')

@section('title', 'Penjualan')

@section('actions')
    @can('manage-sales')
        <a href="{{ route('sales.create') }}" class="btn btn-primary">+ Buat penjualan</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('sales.index') }}" class="filters">
            <div class="field">
                <label for="q">Cari no. transaksi / kasir</label>
                <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Contoh: INV-2026 atau nama kasir" maxlength="100">
            </div>

            <div class="field">
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    <option value="completed" @selected($status === 'completed')>Selesai</option>
                    <option value="cancelled" @selected($status === 'cancelled')>Dibatalkan</option>
                </select>
            </div>

            <div class="field">
                <label for="date_from">Dari tanggal</label>
                <input type="date" id="date_from" name="date_from" value="{{ $dateFrom }}">
            </div>

            <div class="field">
                <label for="date_to">Sampai tanggal</label>
                <input type="date" id="date_to" name="date_to" value="{{ $dateTo }}">
            </div>

            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($search || $status || $dateFrom || $dateTo)
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

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
                    @forelse ($sales as $sale)
                        <tr>
                            <td>{{ $sale->sale_number }}</td>
                            <td>{{ $sale->sold_at->format('d M Y H:i') }}</td>
                            <td>{{ $sale->user->name }}</td>
                            <td class="text-right">Rp {{ number_format($sale->total, 0, ',', '.') }}</td>
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

        <div class="pagination-wrap">{{ $sales->links() }}</div>
    </div>
@endsection
