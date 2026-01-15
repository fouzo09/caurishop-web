<?php

namespace App\Http\Controllers;

use App\Http\Requests\admin\AuthenticationRequest;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login()
    {
        return view('admin.auth.login');
    }

    public function authenticate(AuthenticationRequest $authenticationRequest)
    {
        dd($authenticationRequest);
    }
}
