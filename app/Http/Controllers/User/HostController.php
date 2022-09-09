<?php

namespace App\Http\Controllers\User;

use App\Models\Host;
use Illuminate\Http\Request;
use App\Models\Module\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class HostController extends Controller
{
    public function index(Module $module)
    {
        //
        $hosts = Host::thisUser($module->id)->with('module', function ($query) {
            $query->select(['id', 'name']);
        })->get();

        return $this->success($hosts);
    }

    public function update(Request $request, Host $host)
    {
        $user = $request->user();
        if ($host->user_id == $user->id) {

            if ($user->balance < 1) {
                return $this->error('余额不足');
            }

            $host->update([
                'status' => 'running'
            ]);

            return $this->updated($host);
        } else {
            return $this->error('无权操作');
        }

        return $this->deleted($host);
    }

    public function destroy(Host $host)
    {
        if ($host->user_id == auth()->id()) {

            if ($host->status == 'pending') {

                // 如果上次更新时间大于 5min
                if (time() - strtotime($host->updated_at) > 300) {
                    $host->delete();
                } else {
                    return $this->error('请等待 5 分钟后再试');
                }
            }

            dispatch(new \App\Jobs\Remote\Host($host, 'delete'));
        } else {
            return $this->error('无权操作');
        }

        return $this->deleted($host);
    }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(Request $request, Module $module)
    // {
    //     // User create host
    //     $this->validate($request, [
    //         'name' => 'required|max:255',
    //         'configuration' => 'required|json',
    //     ]);

    //     $data = [
    //         'name' => $request->name,
    //         'module_id' => $module->id,
    //         'configuration' => $request->configuration ?? [],
    //     ];


    //     // if (!$data['confirm']) {
    //     //     $data['confirm'] = false;

    //     // }

    //     // $calc = $module->remotePost('/hosts', ['data' => $data]);
    //     // $data['price'] = $calc[0]['data']['price'];

    //     $host = Host::create($data);
    //     return $this->created($host);

    //     // if ($request->confirm) {
    //     //     $host = Host::create($data);
    //     //     return $this->created($host);
    //     // } else {
    //     //     // return $this->apiResponse($calc[0]['data'], $calc[1]);
    //     // }



    //     // // post to module
    //     // $host = $module->hosts()->create([
    //     //     'name' => $request->name,
    //     //     'configuration' => $request->configuration,
    //     // ]);
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show()
    // {
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(Request $request, $id)
    // {
    //     //
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy($id)
    // {
    //     //
    // }
}
