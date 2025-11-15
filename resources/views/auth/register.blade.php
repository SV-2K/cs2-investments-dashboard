@extends('layouts.auth')

@section('title')Sign in @endsection

@section('form')
    <form method="POST" action="{{ route('signUp') }}">
        @csrf
        <h2>Sign in</h2>
        <div
        @class([
            'mt-2',
            'was-validated' => $errors->has('email')
        ])>
            <input type="email" class="form-control" name="email" placeholder="Email" value="{{ old('email') }}" required>
            @error('email')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <div
            @class([
              'mt-2',
              'was-validated' => $errors->has('name')
            ])>
            <input type="text" class="form-control" name="name" placeholder="Name" value="{{ old('name') }}" required>
            @error('name')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div
        @class([
          'mt-2',
          'was-validated' => $errors->has('steamid64')
        ])>
            <input type="text" class="form-control" name="steamid64" placeholder="SteamID64" value="{{ old('steamid64') }}" required>
            @error('steamid64')
            <div class="invalid-feedback">
                {{ $message }}
            </div>
            @enderror
        </div>
        <div
        @class([
            'mt-2',
            'was-validated' => $errors->has('password')
        ])>
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            @error('password')
                <div class="invalid-feedback">
                    {{ $message }}
                </div>
            @enderror
        </div>
        <input type="password" class="form-control mt-2" name="password_confirmation" placeholder="Repeat password" required>
        <button class="btn btn-primary mt-2" type="submit">Sign in</button>
    </form>
@endsection
