@extends('layouts.app')

@section('title', 'Pembelian')

@section('actions')
    @can('manage-purchases')
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Buat pembelian</a>
    @endcan
@endsection

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('purchases.index') }}" class="filters">
            <div class="field">
                <label for="q">Cari no. transaksi / pemasok</label>
                <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Contoh: PO-2026 atau nama pemasok" maxlength="100">
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
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>No. transaksi</th>
                        <th>Tanggal</th>
                        <th>Pemasok</th>
                        <th>Dibuat oleh</th>
                        <th class="text-right">Total</th>
                        <th>Status</th>
                        <th class="col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_number }}</td>
                            <td>{{ $purchase->purchased_at->format('d M Y H:i') }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>{{ $purchase->user->name }}</td>
                            <td class="text-right">Rp {{ number_format($purchase->total, 0, ',', '.') }}</td>
                            <td>
                                <span class="badge badge-{{ $purchase->status }}">
                                    {{ $purchase->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                                </span>
                            </td>
                            <td class="col-actions">
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-small btn-secondary">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="empty">Belum ada pembelian.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">{{ $purchases->links() }}</div>
    </div>
@endsection
