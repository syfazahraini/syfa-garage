<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * Manajemen pengguna & role (khusus Admin).
 * Akses dijaga oleh middleware "can:manage-users" di routes/web.php.
 */
class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('name')->paginate(10);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        // 'role' tidak ada di $fillable (mass-assignment protection), jadi diisi manual
        $user = new User($request->only('name', 'email', 'password'));
        $user->role = $request->validated('role');
        $user->save();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('users.edit', compact('user'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $newRole = $request->validated('role');

        // Mencegah admin mengunci dirinya sendiri
        if ($user->is($request->user()) && $newRole !== $user->role) {
            return back()->withInput()->with('error', 'Kamu tidak bisa mengubah role akunmu sendiri.');
        }

        $user->fill($request->only('name', 'email'));

        if ($request->filled('password')) {
            $user->password = $request->input('password'); // di-hash otomatis (cast 'hashed')
        }

        $user->role = $newRole;
        $user->save();

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        // Pengguna yang sudah punya transaksi tidak boleh dihapus (menjaga riwayat transaksi)
        if ($user->sales()->exists() || $user->purchases()->exists()) {
            return back()->with('error', 'Pengguna ini sudah punya transaksi sehingga tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus.');
    }
}
