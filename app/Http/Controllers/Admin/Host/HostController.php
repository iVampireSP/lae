<?php

namespace App\Http\Controllers\Admin\Host;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
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
     *
     * @return JsonResponse|Response
     */
    public function store(Request $request, Host $host)
    {
        $this->validate($request, [
            'name' => 'required|max:255',
            'module_id' => 'required|string|exists:modules,id',
            'price' => 'required|numeric',
        ]);

        // if route has user
        if ($request->route('user')) {
            $user_id = $request->route('user');
        } else {
            $this->validate($request, [
                'user_id' => 'required|integer|exists:users,id',
            ]);
            $user_id = $request->user_id;
        }

        $data = [
            'name' => $request->name,
            'module_id' => $request->module_id,
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
     * @param Host $host
     *
     * @return JsonResponse
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
     * @param  \Illuminate\Http\Request $request
     * @param Host                      $host
     *
     * @return Response
     */
    public function update(Request $request, Host $host)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Host $host
     *
     * @return Response
     */
    public function destroy(Host $host)
    {
        //
    }
}
