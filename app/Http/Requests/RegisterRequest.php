<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'name' => ['required', 'string'],
            'steamid64' => ['required', 'string'],
            'password' => ['required', 'min:8', 'confirmed'],
        ];
    }
}
