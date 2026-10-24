@extends('layouts.app')

@section('title', 'Ubah Kategori')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('categories.update', $category) }}" novalidate>
            @csrf
            @method('PUT')

            @include('categories._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
