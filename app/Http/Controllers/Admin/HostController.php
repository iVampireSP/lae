<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Host $host)
    {
        $host->load('user');
        $hosts = $host->paginate(100);

        return view('admin.hosts.index', compact('hosts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function show(Host $host)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function edit(Host $host): View
    {
        //

        return view('admin.hosts.edit', compact('host'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Host $host)
    {
        //

        $request->validate([
            'name' => 'required|string|max:255',
            'managed_price' => 'nullable|numeric',
        ]);

        $host->update($request->all());

        return back()->with('success', '此主机已更新。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Host  $host
     * @return \Illuminate\Http\Response
     */
    public function destroy(Host $host)
    {
        //
    }
}
