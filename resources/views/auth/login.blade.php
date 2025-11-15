@extends('layouts.auth')

@section('title')Log in @endsection

@section('form')
    <form
        @class([
            'was-validated' => $errors->any()
        ])
        class="needs-validation" method="POST" action="{{ route('signIn') }}">
        @csrf
        <h2>Log in</h2>
        <input type="email" class="form-control" name="email" placeholder="Email" required>
        <div class="mt-2">
            <input type="password" class="form-control" name="password" placeholder="Password" required>
            @if($errors->any())
                <div class="invalid-feedback">
                    Invalid email or password
                </div>
            @endif
        </div>
        <button class="btn btn-primary mt-2" type="submit">Log in</button>
    </form>
@endsection
