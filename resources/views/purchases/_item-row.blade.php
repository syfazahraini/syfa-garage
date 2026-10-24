{{-- Satu baris item pembelian. Dipakai untuk baris awal dan sebagai template tombol "Tambah item". --}}
<tr class="item-row">
    <td>
        <select name="items[{{ $index }}][product_id]"
                class="item-product @if ($errors->has("items.$index.product_id")) is-invalid @endif" required>
            <option value="">— Pilih produk —</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}"
                        data-cost="{{ (float) $product->purchase_price }}"
                        @selected((string) ($item['product_id'] ?? '') === (string) $product->id)>
                    {{ $product->sku }} — {{ $product->name }}
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
    <td>
        <input type="number" name="items[{{ $index }}][unit_cost]" class="item-cost @if ($errors->has("items.$index.unit_cost")) is-invalid @endif"
               min="0" step="0.01" value="{{ $item['unit_cost'] ?? '' }}" required>
        @if ($errors->has("items.$index.unit_cost"))
            <span class="field-error">{{ $errors->first("items.$index.unit_cost") }}</span>
        @endif
    </td>
    <td class="text-right item-subtotal">Rp 0</td>
    <td class="col-actions">
        <button type="button" class="btn btn-small btn-danger remove-row">Hapus</button>
    </td>
</tr>
