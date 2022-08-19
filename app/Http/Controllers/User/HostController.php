<?php

namespace App\Http\Controllers\User;

use App\Models\Host;
use Illuminate\Http\Request;
use App\Models\Module\Module;
use App\Http\Controllers\Controller;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Module $module)
    {
        //
        $hosts = Host::thisUser($module->id)->get();

        return $this->success($hosts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Module $module)
    {
        // User create host
        $request->validate([
            'name' => 'required|max:255',
            'configuration' => 'required|json',
        ]);

        // // post to module
        // $host = $module->hosts()->create([
        //     'name' => $request->name,
        //     'configuration' => $request->configuration,
        // ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
