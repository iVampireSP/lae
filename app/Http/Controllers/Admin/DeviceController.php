<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\EmqxSupportException;
use App\Http\Controllers\Controller;
use App\Jobs\Support\EMQXKickClientJob;
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

    public function destroy(Request $request): RedirectResponse
    {
        $emqx = new EmqxSupport();

        if ($request->filled('client_id')) {
            $emqx->kickClient($request->input('client_id'));
        }

        if ($request->filled('username')) {
            $username = $request->input('username');
            $module_name = explode('.', $username)[0];

            $this->dispatch(new EMQXKickClientJob(null, $module_name, false));
            $this->dispatch(new EMQXKickClientJob(null, $module_name . '.', true));
        }

        return back()->with('success', '正在让它们下线。');
    }
}
