<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Host;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     *
     * @param Request $request
     *
     * @return View
     */
    public function index(Request $request): View
    {
        $hosts = Host::with('user');

        // 遍历所有的搜索条件
        foreach (['name', 'module_id', 'status', 'user_id'] as $field) {
            if ($request->has($field)) {
                $hosts->where($field, 'like', '%' . $request->input($field) . '%');
            }
        }

        $hosts = $hosts->paginate(100)->withQueryString();;

        return view('admin.hosts.index', compact('hosts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Host $host
     *
     * @return View
     */
    public function edit(Host $host): View
    {
        //

        return view('admin.hosts.edit', compact('host'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Host    $host
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Host $host): RedirectResponse
    {
        //

        $request->validate([
            // 'name' => 'required|string|max:255',
            'managed_price' => 'nullable|numeric',
        ]);

        $req = $request->only('managed_price');

        $host->update($req);

        return back()->with('success', '此主机已更新。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Host $host
     *
     * @return RedirectResponse
     */
    public function destroy(Host $host): RedirectResponse
    {
        $host->safeDelete();

        return redirect()->route('admin.hosts.index')->with('success', '正在排队删除此主机。');
    }
}
