<?php

namespace App\Http\Controllers\Remote;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProviderController extends Controller
{
    public function index()
    {
        return $this->success(auth('remote')->user());
    }
}
