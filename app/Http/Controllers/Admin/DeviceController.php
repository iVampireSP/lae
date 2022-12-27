<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EmqxSupportException;
use App\Http\Controllers\Controller;
use App\Support\EmqxSupport;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;

class DeviceController extends Controller
{
    //

    public function index(Request $request)
    {
        $emqx = new EmqxSupport();

        try {
            $clients = $emqx->clients([
                'clientid' => $request->client_id,
                'username' => $request->username,
                'page' => $request->page,
            ]);
        } catch (EmqxSupportException|ConnectionException $e) {
            return back()->with('error', $e->getMessage());
        }

        // dd($clients);
        return view('admin.device.index', compact('clients'));
    }

    //
    // public function show(Request $request, $client_id)
    // {
    //     $emqx = new EmqxSupport();
    //
    //     $client = $emqx->clients(['clientid' => $client_id]);
    //
    //     return view('admin.device.show', compact('client'));
    // }


    public function destroy($client_id)
    {
        $emqx = new EmqxSupport();

        $emqx->kickClient($client_id);

        return back()->with('success', '此客户端已下线。');
    }
}
