@extends('layouts.app')

@section('title', 'Pemasok')

@section('actions')
    <a href="{{ route('suppliers.create') }}" class="btn btn-primary">+ Tambah pemasok</a>
@endsection

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('suppliers.index') }}" class="filters">
            <div class="field">
                <label for="q">Cari pemasok</label>
                <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Nama, telepon, atau email" maxlength="100">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($search)
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Telepon</th>
                        <th>Email</th>
                        <th class="col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td>{{ $supplier->email ?: '-' }}</td>
                            <td class="col-actions">
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-small btn-secondary">Ubah</a>

                                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" class="inline-form"
                                      onsubmit="return confirm('Hapus pemasok ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty">Pemasok tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">{{ $suppliers->links() }}</div>
    </div>
@endsection
