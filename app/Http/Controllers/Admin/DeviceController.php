<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EmqxSupportException;
use App\Http\Controllers\Controller;
use App\Support\EmqxSupport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeviceController extends Controller
{
    public function index(Request $request): RedirectResponse|View
    {
        $emqx = new EmqxSupport();

        try {
            $clients = $emqx->clients([
                'clientid' => $request->input('client_id'),
                'username' => $request->input('username'),
                'page' => $request->input('page'),
            ]);
        } catch (EmqxSupportException $e) {
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


    /**
     * @throws EmqxSupportException
     */
    public function destroy($client_id): RedirectResponse
    {
        $emqx = new EmqxSupport();

        $emqx->kickClient($client_id);

        return back()->with('success', '此客户端已下线。');
    }
}
