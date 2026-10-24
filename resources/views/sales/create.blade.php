@extends('layouts.app')

@section('title', 'Buat Penjualan')

@section('content')
    @php
        // Saat ada error, baris item yang sudah diisi dikembalikan lagi
        $oldItems = old('items');
        if (! is_array($oldItems) || $oldItems === []) {
            $oldItems = [['product_id' => '', 'quantity' => 1]];
        }
        $nextIndex = (int) collect($oldItems)->keys()->filter(fn ($key) => is_numeric($key))->max() + 1;
    @endphp

    <form method="POST" action="{{ route('sales.store') }}" novalidate>
        @csrf

        <div class="card">
            <h3 class="section-title section-title-first">Item penjualan</h3>

            @error('items')
                <div class="alert alert-error">{{ $message }}</div>
            @enderror

            <div class="table-wrap">
                <table class="table table-items">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="col-qty">Jumlah</th>
                            <th class="col-money text-right">Harga jual</th>
                            <th class="col-money text-right">Subtotal</th>
                            <th class="col-actions"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body" data-next-index="{{ $nextIndex }}">
                        @foreach ($oldItems as $index => $item)
                            @include('sales._item-row', ['index' => $index, 'item' => $item, 'products' => $products])
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total</strong></td>
                            <td class="text-right"><strong id="grand-total">Rp 0</strong></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <button type="button" id="add-row" class="btn btn-secondary">+ Tambah item</button>
            <p class="field-hint">
                Harga diambil dari data produk dan total dihitung oleh server. Penjualan ditolak jika jumlah melebihi stok.
            </p>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan penjualan</button>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </form>

    {{-- Template baris baru untuk tombol "Tambah item" --}}
    <template id="row-template">
        @include('sales._item-row', ['index' => '__INDEX__', 'item' => [], 'products' => $products])
    </template>
@endsection

@push('scripts')
<script>
(function () {
    var tbody = document.getElementById('items-body');
    var template = document.getElementById('row-template');
    var totalEl = document.getElementById('grand-total');
    var nextIndex = parseInt(tbody.dataset.nextIndex, 10);

    function rupiah(n) {
        return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n);
    }

    function priceOf(row) {
        var select = row.querySelector('.item-product');
        var option = select.options[select.selectedIndex];
        return (option && option.value && option.dataset.price) ? parseFloat(option.dataset.price) : 0;
    }

    function recalc() {
        var total = 0;
        tbody.querySelectorAll('.item-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            var price = priceOf(row);
            var sub = qty * price;
            row.querySelector('.item-price').textContent = rupiah(price);
            row.querySelector('.item-subtotal').textContent = rupiah(sub);
            total += sub;
        });
        totalEl.textContent = rupiah(total);
    }

    document.getElementById('add-row').addEventListener('click', function () {
        var html = template.innerHTML.split('__INDEX__').join(String(nextIndex));
        nextIndex++;
        tbody.insertAdjacentHTML('beforeend', html);
        recalc();
    });

    tbody.addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            if (tbody.querySelectorAll('.item-row').length > 1) {
                e.target.closest('.item-row').remove();
                recalc();
            }
        }
    });

    tbody.addEventListener('change', recalc);
    tbody.addEventListener('input', recalc);
    recalc();
})();
</script>
@endpush
