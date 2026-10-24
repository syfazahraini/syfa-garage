@extends('layouts.app')

@section('title', 'Tambah Pemasok')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('suppliers.store') }}" novalidate>
            @csrf

            @include('suppliers._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
