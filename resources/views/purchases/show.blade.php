@extends('layouts.app')

@section('title', 'Detail Pembelian')

@section('actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Kembali ke daftar</a>
@endsection

@section('content')
    <div class="card">
        <dl class="detail-list">
            <dt>No. transaksi</dt>
            <dd>{{ $purchase->purchase_number }}</dd>

            <dt>Tanggal</dt>
            <dd>{{ $purchase->purchased_at->format('d M Y H:i') }} WIB</dd>

            <dt>Pemasok</dt>
            <dd>{{ $purchase->supplier->name }}</dd>

            <dt>Dibuat oleh</dt>
            <dd>{{ $purchase->user->name }}</dd>

            <dt>Status</dt>
            <dd>
                <span class="badge badge-{{ $purchase->status }}">
                    {{ $purchase->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                </span>
            </dd>
        </dl>

        <h3 class="section-title">Item pembelian</h3>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Produk</th>
                        <th class="text-right">Jumlah</th>
                        <th class="text-right">Harga beli</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchase->items as $item)
                        <tr>
                            <td>{{ $item->product->sku }}</td>
                            <td>{{ $item->product->name }}</td>
                            <td class="text-right">{{ $item->quantity }}</td>
                            <td class="text-right">Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right"><strong>Total</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($purchase->total, 0, ',', '.') }}</strong></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @can('cancel-transactions')
            @if ($purchase->status === 'completed')
                <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" class="cancel-form"
                      onsubmit="return confirm('Batalkan pembelian ini? Stok produk akan dikurangi kembali.')">
                    @csrf
                    <button type="submit" class="btn btn-danger">Batalkan pembelian</button>
                </form>
            @endif
        @endcan
    </div>
@endsection
