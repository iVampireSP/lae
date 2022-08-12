<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Admin $admin)
    {
        $admin = $admin->simplePaginate(10);
        return $this->success($admin);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // password required
        $this->validate($request, [
            'password' => 'required',
        ]);

        $admin = Admin::create($request->all());
        return $this->success($admin);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Admin\Admin  $Admin
     * @return \Illuminate\Http\Response
     */
    public function show(Admin $admin)
    {
        return $this->success($admin);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Admin\Admin  $Admin
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Admin $admin)
    {
        // 
        $admin->update($request->all());

        return $this->updated($admin);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Admin\Admin  $Admin
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admin $admin)
    {
        // soft delete Admin
        $admin->delete();
        return $this->deleted($admin);
    }
}
