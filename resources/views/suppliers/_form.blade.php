{{-- Field form pemasok. Dipakai oleh create dan edit. $supplier hanya ada saat edit. --}}
<div class="field">
    <label for="name">Nama pemasok</label>
    <input type="text" id="name" name="name" value="{{ old('name', $supplier->name ?? '') }}"
           class="@error('name') is-invalid @enderror" maxlength="150" required autofocus>
    @error('name')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="phone">Telepon <span class="muted">(opsional)</span></label>
    <input type="text" id="phone" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}"
           class="@error('phone') is-invalid @enderror" maxlength="30">
    @error('phone')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="email">Email <span class="muted">(opsional)</span></label>
    <input type="email" id="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
           class="@error('email') is-invalid @enderror" maxlength="255">
    @error('email')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>
