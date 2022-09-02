<?php

namespace App\Http\Controllers\User;

use App\Models\Host;
use Illuminate\Http\Request;
use App\Models\Module\Module;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class HostController extends Controller
{
    public function __invoke(Module $module)
    {
        //
        $hosts = Host::thisUser($module->id)->with('module', function ($query) {
            $query->select(['id', 'name']);
        })->get();

        return $this->success($hosts);
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
    //     $request->validate([
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
