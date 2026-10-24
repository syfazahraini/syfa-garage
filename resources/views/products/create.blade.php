@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('products.store') }}" novalidate>
            @csrf

            @include('products._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
