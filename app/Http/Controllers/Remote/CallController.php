<?php

namespace App\Http\Controllers\Remote;

use App\Models\Host;
use Illuminate\Http\Request;
use App\Models\Module\Module;
use App\Http\Controllers\Controller;

class CallController extends Controller
{
    // invoke the remote method
    public function host(Request $request, Host $host, $func) {
        $host->load('module');
        $response = $host->module->remoteHost($host->id, $func, $request->all());

        return $this->apiResponse($response[0], $response[1]);
    }

    public function module(Request $request, Module $module, $func)
    {
        $response = $module->remote($func, $request->all());

        return $this->apiResponse($response[0], $response[1]);
    }
}
