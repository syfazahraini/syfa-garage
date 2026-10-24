{{-- Satu baris item penjualan. Dipakai untuk baris awal dan sebagai template tombol "Tambah item". --}}
<tr class="item-row">
    <td>
        <select name="items[{{ $index }}][product_id]"
                class="item-product @if ($errors->has("items.$index.product_id")) is-invalid @endif" required>
            <option value="">— Pilih produk —</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}"
                        data-price="{{ (float) $product->selling_price }}"
                        @selected((string) ($item['product_id'] ?? '') === (string) $product->id)>
                    {{ $product->sku }} — {{ $product->name }} (stok {{ $product->stock }})
                </option>
            @endforeach
        </select>
        @if ($errors->has("items.$index.product_id"))
            <span class="field-error">{{ $errors->first("items.$index.product_id") }}</span>
        @endif
    </td>
    <td>
        <input type="number" name="items[{{ $index }}][quantity]" class="item-qty @if ($errors->has("items.$index.quantity")) is-invalid @endif"
               min="1" step="1" value="{{ $item['quantity'] ?? 1 }}" required>
        @if ($errors->has("items.$index.quantity"))
            <span class="field-error">{{ $errors->first("items.$index.quantity") }}</span>
        @endif
    </td>
    <td class="text-right item-price">Rp 0</td>
    <td class="text-right item-subtotal">Rp 0</td>
    <td class="col-actions">
        <button type="button" class="btn btn-small btn-danger remove-row">Hapus</button>
    </td>
</tr>
