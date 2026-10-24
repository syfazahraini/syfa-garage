@extends('layouts.app')

@section('title', 'Buat Pembelian')

@section('content')
    @php
        // Saat validasi gagal, baris item yang sudah diisi dikembalikan lagi
        $oldItems = old('items');
        if (! is_array($oldItems) || $oldItems === []) {
            $oldItems = [['product_id' => '', 'quantity' => 1, 'unit_cost' => '']];
        }
        $nextIndex = (int) collect($oldItems)->keys()->filter(fn ($key) => is_numeric($key))->max() + 1;
    @endphp

    <form method="POST" action="{{ route('purchases.store') }}" novalidate>
        @csrf

        <div class="card">
            <div class="field field-narrow">
                <label for="supplier_id">Pemasok</label>
                <select id="supplier_id" name="supplier_id" class="@error('supplier_id') is-invalid @enderror" required>
                    <option value="">— Pilih pemasok —</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}" @selected((string) old('supplier_id') === (string) $supplier->id)>
                            {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                @error('supplier_id')
                    <span class="field-error">{{ $message }}</span>
                @enderror
            </div>

            <h3 class="section-title">Item pembelian</h3>

            @error('items')
                <div class="alert alert-error">{{ $message }}</div>
            @enderror

            <div class="table-wrap">
                <table class="table table-items">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="col-qty">Jumlah</th>
                            <th class="col-money">Harga beli (Rp)</th>
                            <th class="col-money text-right">Subtotal</th>
                            <th class="col-actions"></th>
                        </tr>
                    </thead>
                    <tbody id="items-body" data-next-index="{{ $nextIndex }}">
                        @foreach ($oldItems as $index => $item)
                            @include('purchases._item-row', ['index' => $index, 'item' => $item, 'products' => $products])
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
            <p class="field-hint">Total di atas hanya perkiraan. Saat disimpan, total dihitung ulang oleh server.</p>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan pembelian</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </div>
    </form>

    {{-- Template baris baru untuk tombol "Tambah item" --}}
    <template id="row-template">
        @include('purchases._item-row', ['index' => '__INDEX__', 'item' => [], 'products' => $products])
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

    function recalc() {
        var total = 0;
        tbody.querySelectorAll('.item-row').forEach(function (row) {
            var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            var cost = parseFloat(row.querySelector('.item-cost').value) || 0;
            var sub = qty * cost;
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

    // Saat produk dipilih, isi harga beli dengan harga beli produk (bisa diubah manual)
    tbody.addEventListener('change', function (e) {
        if (e.target.classList.contains('item-product')) {
            var option = e.target.options[e.target.selectedIndex];
            var costInput = e.target.closest('.item-row').querySelector('.item-cost');
            if (option && option.value && option.dataset.cost !== undefined) {
                costInput.value = option.dataset.cost;
            }
            recalc();
        }
    });

    tbody.addEventListener('input', recalc);
    recalc();
})();
</script>
@endpush
