@extends('layouts.app')

@section('title', 'Ubah Pengguna')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('users.update', $user) }}" novalidate>
            @csrf
            @method('PUT')

            @include('users._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
