<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\Cluster\Monitor;
use App\Http\Controllers\Controller;
use App\Support\ClusterSupport;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\HttpFoundation\StreamedResponse;

class NodeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $nodes = ClusterSupport::nodes();

        return view('admin.cluster.nodes', compact('nodes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  string  $node
     * @return JsonResponse
     */
    public function update(Request $request, string $node): JsonResponse
    {
        $request->validate([
            'weight' => 'sometimes|integer|min:0|max:100',
        ]);

        ClusterSupport::update($node, [
            'weight' => $request->input('weight'),
        ]);

        return $this->success('Updated');
    }

    public function event(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'restart' => 'nullable|string|max:10|in:web,queue',
        ]);

        if ($service = $request->input('restart')) {
            if ($service === 'web') {
                ClusterSupport::publish('cluster.restart.'.$service);
            } elseif ($service === 'queue') {
                Artisan::call('queue:restart');
            }
        }

        return back()->with('success', '已经向集群广播命令。');
    }

    public function stream(): StreamedResponse
    {
        $response = new StreamedResponse(function () {
            ClusterSupport::publish('monitor.started');

            ClusterSupport::listen('*', function ($event, $message) {
                $monitor = new Monitor();
                if (connection_aborted()) {
                    return;
                }

                $msg = $monitor->format($event, $message, false);

                echo 'data: '.$msg."\n\n";

                ob_flush();
                flush();
            }, false);
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cache-Control', 'no-cache');

        return $response;
    }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int  $id
    //  * @return Response
    //  */
    // public function destroy($id)
    // {
    //     //
    // }
}
