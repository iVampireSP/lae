<?php

namespace App\Http\Controllers\Remote;

use App\Http\Controllers\Controller;
use App\Models\Module\Module;
use Illuminate\Http\Request;

class CallController extends Controller
{
    // invoke the remote method
    public function __invoke(Request $request, Module $module) {
        $request->validate([
            'func' => 'required|string|max:255',
        ]);

        $response = $module->remote($request->func, $request->all());

        return $this->apiResponse($response[0], $response[1]);
    }
}
