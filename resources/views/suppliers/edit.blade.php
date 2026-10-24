@extends('layouts.app')

@section('title', 'Ubah Pemasok')

@section('content')
    <div class="card card-form">
        <form method="POST" action="{{ route('suppliers.update', $supplier) }}" novalidate>
            @csrf
            @method('PUT')

            @include('suppliers._form')

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
