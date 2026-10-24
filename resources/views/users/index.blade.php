@extends('layouts.app')

@section('title', 'Pengguna')

@section('actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary">+ Tambah pengguna</a>
@endsection

@section('content')
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Dibuat</th>
                        <th class="col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td class="col-actions">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-small btn-secondary">Ubah</a>

                                @if (! $user->is(auth()->user()))
                                    <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline-form"
                                          onsubmit="return confirm('Hapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="empty">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">{{ $users->links() }}</div>
    </div>
@endsection
