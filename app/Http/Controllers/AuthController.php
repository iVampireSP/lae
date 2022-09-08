<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // use ApiResponse;

    public function index(Request $request)
    {

        // auth check
        if (Auth::check()) {
            dd(Auth::user());
        } else {
            dd('n');
        }

    }

}
