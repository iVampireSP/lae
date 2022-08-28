<?php

namespace App\Http\Controllers\Remote\Host;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Cache;
use Illuminate\Http\Request;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        Host::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 保存服务器
        $request->validate([
            'name' => 'required|string',
            'ip' => 'sometimes|ip',
            // status only allow online or offline
            'status' => 'required|in:online,offline,maintenance',
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  Host $host
     * @return \Illuminate\Http\Response
     */
    public function show(Host $host)
    {

        return $this->success($host);
        //

        // dd($host->cost());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Host $host
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Host $host)
    {
        //
        $request->validate([
            'status' => 'sometimes|in:stopped,running,suspended,error',
            'managed_price' => 'sometimes|numeric|nullable',

            // 如果是立即扣费
            'cost_once' => 'sometimes|boolean|nullable',
        ]);

        // if has cost_once
        if ($request->has('cost_once')) {
            $host->cost($request->cost_once);

            return $this->updated($request->cost_once);
        }

        $host->update($request->all());

        return $this->updated($host);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Host $host
     * @return \Illuminate\Http\Response
     */
    public function destroy(Host $host)
    {
        //
        $host->delete();

        // 

        return $this->deleted($host);
    }
}
