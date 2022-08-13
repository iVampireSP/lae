<?php

namespace App\Http\Controllers\Admin\Host;

use App\Models\User;
use App\Models\User\Host;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $hosts = new Host();
        // if route has user
        if ($request->route('user')) {
            $hosts = $hosts->where('user_id', $request->route('user'));
        }

        $hosts = $hosts->simplePaginate(10);

        return $this->success($hosts);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Host $host)
    {
        $request->validate([
            'name' => 'required|max:255',
            'provider_module_id' => 'required|integer|exists:provider_modules,id',
            'price' => 'required|numeric',
        ]);

        // if route has user
        if ($request->route('user')) {
            $user_id = $request->route('user');
        } else {
            $request->validate([
                'user_id' => 'required|integer|exists:users,id',
            ]);
            $user_id = $request->user_id;
        }

        $data = [
            'name' => $request->name,
            'provider_module_id' => $request->provider_module_id,
            'user_id' => $user_id,
            'price' => $request->price,
            'configuration' => $request->configuration ?? [],
            'status' => $request->status ?? 'pending',
        ];


        $host = $host->create($data);

        return $this->created($host);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function show(User $user, Host $host)
    {
        //
        // $host->load('providerModule');
        return $this->success($host);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Host $host)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function destroy(Host $host)
    {
        //
    }
}
