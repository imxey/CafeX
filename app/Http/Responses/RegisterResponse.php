<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request)
    {
       return redirect()->route('questionnaire')->with('status', 'Registration successful! Please login.');
    }
}
