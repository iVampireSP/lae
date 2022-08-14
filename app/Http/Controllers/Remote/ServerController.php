<?php

namespace App\Http\Controllers\Remote;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Remote\Server\StatusRequest;
use App\Models\Server\Status;
use Illuminate\Support\Facades\Cache;

class ServerController extends Controller
{
    // protected $cache;

    // public function __construct() {
    //     $this->cache = Cache::tags(['remote']);
        
    //     // 临时修改 prefix
    //     $this->cache->setPrefix('remote_' . auth('remote')->id());
    // }

    // public function all() {
    //     return $this->cache->get('servers', function () {
    //         return [];
    //     });
    // }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // // 
        // $servers = $this->cache->get('servers', function () {
        //     return [];
        // });

        $servers = Status::module()->get();

        return $this->success($servers);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Status $server)
    {
        // 
        $request->validate([
            'name' => 'required|string',
            'ip' => 'sometimes|ip',
            // status only allow online or offline
            'status' => 'required|in:online,offline,maintenance',
        ]);

        $server = $server->create([
            'name' => $request->name,
            'ip' => $request->ip,
            'status' => $request->status,
            'module_id' => auth('remote')->id()
        ]);

        return $this->success($server);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StatusRequest $request, Status $server)
    {
        // only allow name,ip,status
        $request->validate([
            'name' => 'sometimes|string',
            'ip' => 'sometimes|ip',
            'status' => 'sometimes|in:online,offline,maintenance',
        ]);

        $server->update($request->only(['name', 'ip', 'status']));

        return $this->updated($server);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Status $status)
    {
        // delete
        $status->provider()->delete();
        return $this->deleted();

    }
}
