<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function signIn(LoginRequest $request)
    {
        $userInput = $request->safe()->only(['email', 'password']);
        $isRemember = (bool)$request->remember_me;

        if (auth()->attempt($userInput, $isRemember)) {
            return redirect()->route('testRoute123');
        } else {
            return redirect('login')->withErrors([]);
        }
    }

    public function register(): View
    {
        return view('auth.register');
    }

    public function signUp(RegisterRequest $request)
    {
        User::create($request->validated());
        if (auth()->attempt($request->validated())) {
            return redirect()->route('testRoute123');
        } else {
            return redirect('register')->withErrors(['form_error' => '']);
        }
    }
}
