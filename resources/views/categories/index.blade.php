@extends('layouts.app')

@section('title', 'Kategori')

@section('actions')
    <a href="{{ route('categories.create') }}" class="btn btn-primary">+ Tambah kategori</a>
@endsection

@section('content')
    <div class="card">
        <form method="GET" action="{{ route('categories.index') }}" class="filters">
            <div class="field">
                <label for="q">Cari kategori</label>
                <input type="text" id="q" name="q" value="{{ $search }}" placeholder="Nama kategori" maxlength="100">
            </div>
            <button type="submit" class="btn btn-primary">Cari</button>
            @if ($search)
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jumlah produk</th>
                        <th class="col-actions">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td class="col-actions">
                                <a href="{{ route('categories.edit', $category) }}" class="btn btn-small btn-secondary">Ubah</a>

                                <form method="POST" action="{{ route('categories.destroy', $category) }}" class="inline-form"
                                      onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-small btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="empty">Kategori tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrap">{{ $categories->links() }}</div>
    </div>
@endsection
