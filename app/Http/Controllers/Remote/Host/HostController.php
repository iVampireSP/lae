<?php

namespace App\Http\Controllers\Remote\Host;

use App\Models\Host;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

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
        // Host::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // 存储计费项目
        $this->validate($request, [
            'status' => 'required|in:running,stopped,error,suspended,pending',
            'price' => 'required|numeric',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        // 如果没有 name，则随机
        $name = $request->input('name', Str::random(10));

        $data = [
            'name' => $name,
            'status' => $request->status,
            'price' => $request->price,
            'user_id' => $request->user_id,
            'module_id' => auth('remote')->id()
        ];

        $host = Host::create($data);

        $host['host_id'] = $host->id;

        return $this->created($host);
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
        $this->validate($request, [
            'status' => 'sometimes|in:running,stopped,error,suspended,pending',
            // 'managed_price' => 'sometimes|numeric|nullable',

            // 如果是立即扣费
            'cost_once' => 'sometimes|numeric|nullable',

            'cost_balance' => 'sometimes|numeric|nullable',
        ]);

        // if has cost_once
        if ($request->has('cost_once')) {
            $host->cost($request->cost_once ?? 0, false);

            return $this->updated();
        }

        if ($request->has('cost_balance')) {
            $host->costBalance($request->cost_balance ?? 0);

            return $this->updated();
        }


        $update = $request->all();
        // module_id 不能被更新
        unset($update['module_id']);
        unset($update['user_id']);

        $host->update($update);

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

        return $this->deleted($host);
    }
}
