{{-- Field form pengguna. Dipakai oleh create dan edit. $user hanya ada saat edit. --}}
<div class="field">
    <label for="name">Nama</label>
    <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}"
           class="@error('name') is-invalid @enderror" required>
    @error('name')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="email">Email</label>
    <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}"
           class="@error('email') is-invalid @enderror" required>
    @error('email')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="role">Role</label>
    <select id="role" name="role" class="@error('role') is-invalid @enderror" required>
        @foreach (['admin' => 'Admin / Owner', 'staff' => 'Staff (kasir/operasional)', 'user' => 'User (pelanggan, hanya melihat)'] as $value => $label)
            <option value="{{ $value }}" @selected(old('role', $user->role ?? 'staff') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    @error('role')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="password">Password</label>
    <input type="password" id="password" name="password"
           class="@error('password') is-invalid @enderror"
           autocomplete="new-password" @if (! isset($user)) required @endif>
    @isset($user)
        <span class="field-hint">Kosongkan jika tidak ingin mengganti password.</span>
    @endisset
    @error('password')
        <span class="field-error">{{ $message }}</span>
    @enderror
</div>

<div class="field">
    <label for="password_confirmation">Konfirmasi password</label>
    <input type="password" id="password_confirmation" name="password_confirmation"
           autocomplete="new-password" @if (! isset($user)) required @endif>
</div>
