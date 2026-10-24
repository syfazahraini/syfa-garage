@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}"
                   class="@error('email') is-invalid @enderror"
                   required autofocus autocomplete="username">
            @error('email')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   class="@error('password') is-invalid @enderror"
                   required autocomplete="current-password">
            @error('password')
                <span class="field-error">{{ $message }}</span>
            @enderror
        </div>

        <label class="checkbox">
            <input type="checkbox" name="remember" value="1"> Ingat saya
        </label>

        <button type="submit" class="btn btn-primary btn-block">Masuk</button>
    </form>
@endsection
