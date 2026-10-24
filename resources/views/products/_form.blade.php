{{-- Field form produk. Dipakai oleh create dan edit. $product hanya ada saat edit. --}}
<div class="field">
    <label for="sku">SKU</label>
    <input type="text" id="sku" name="sku" value="{{ old('sku', $product->sku ?? '') }}"
           class="@error('sku') is-invalid @enderror" maxlength="50" required autofocus>
    <span class="field-hint">Kode unik produk, misalnya OLI-005. Otomatis diubah ke huruf besar.</span>
    @error('sku')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="name">Nama produk</label>
    <input type="text" id="name" name="name" value="{{ old('name', $product->name ?? '') }}"
           class="@error('name') is-invalid @enderror" maxlength="150" required>
    @error('name')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="category_id">Kategori</label>
    <select id="category_id" name="category_id" class="@error('category_id') is-invalid @enderror" required>
        <option value="">— Pilih kategori —</option>
        @foreach ($categories as $category)
            <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>
                {{ $category->name }}
            </option>
        @endforeach
    </select>
    @error('category_id')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="purchase_price">Harga beli (Rp)</label>
    <input type="number" id="purchase_price" name="purchase_price" min="0" step="0.01"
           value="{{ old('purchase_price', isset($product) ? (float) $product->purchase_price : '') }}"
           class="@error('purchase_price') is-invalid @enderror" required>
    @error('purchase_price')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="selling_price">Harga jual (Rp)</label>
    <input type="number" id="selling_price" name="selling_price" min="0" step="0.01"
           value="{{ old('selling_price', isset($product) ? (float) $product->selling_price : '') }}"
           class="@error('selling_price') is-invalid @enderror" required>
    @error('selling_price')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="minimum_stock">Stok minimum</label>
    <input type="number" id="minimum_stock" name="minimum_stock" min="0" step="1"
           value="{{ old('minimum_stock', $product->minimum_stock ?? 0) }}"
           class="@error('minimum_stock') is-invalid @enderror" required>
    <span class="field-hint">Produk masuk daftar "stok rendah" jika stoknya sama dengan atau di bawah angka ini.</span>
    @error('minimum_stock')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label>Stok saat ini</label>
    <div class="readonly">{{ $product->stock ?? 0 }}</div>
    <span class="field-hint">Stok tidak diubah di sini. Stok bertambah lewat pembelian dan berkurang lewat penjualan.</span>
</div>
