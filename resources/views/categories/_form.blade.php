{{-- Field form kategori. Dipakai oleh create dan edit. $category hanya ada saat edit. --}}
<div class="field">
    <label for="name">Nama kategori</label>
    <input type="text" id="name" name="name" value="{{ old('name', $category->name ?? '') }}"
           class="@error('name') is-invalid @enderror" maxlength="100" required autofocus>
    @error('name')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>
