@extends('layouts.app')

@section('title', 'Ubah Produk')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('products.update', $product) }}" novalidate>
            @csrf
            @method('PUT')

            @include('products._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                <a href="{{ route('products.show', $product) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
