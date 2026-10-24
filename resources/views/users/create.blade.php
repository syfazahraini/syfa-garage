@extends('layouts.app')

@section('title', 'Tambah Pengguna')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('users.store') }}" novalidate>
            @csrf

            @include('users._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
