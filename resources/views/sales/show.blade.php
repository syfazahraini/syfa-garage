@extends('layouts.app')

@section('title', 'Detail Penjualan')

@section('actions')
    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Kembali ke daftar</a>
@endsection

@section('content')
    <div class="card">
        <dl class="detail-list">
            <dt>No. transaksi</dt>
            <dd>{{ $sale->sale_number }}</dd>

            <dt>Tanggal</dt>
            <dd>{{ $sale->sold_at->format('d M Y H:i') }} WIB</dd>

            <dt>Kasir</dt>
            <dd>{{ $sale->user->name }}</dd>

            <dt>Status</dt>
            <dd>
                <span class="badge badge-{{ $sale->status }}">
                    {{ $sale->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                </span>
            </dd>
        </dl>

        <h3 class="section-title">Item penjualan</h3>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produk</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Harga jual</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>{{ $item->product->sku }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($sale->total, 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @can('cancel-transactions')
            @if ($sale->status === 'completed')
                <form method="POST" action="{{ route('sales.cancel', $sale) }}" class="cancel-form"
                      onsubmit="return confirm('Batalkan penjualan ini? Stok produk akan dikembalikan.')">
                    @csrf
                    <button type="submit" class="btn btn-danger">Batalkan penjualan</button>
                </form>
            @endif
        @endcan
    </div>
@endsection
