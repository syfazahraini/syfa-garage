@extends('layouts.app')

@section('title', 'Tambah Kategori')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('categories.store') }}" novalidate>
            @csrf

            @include('categories._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
