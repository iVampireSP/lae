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
     */
    public function index(Request $request): View
    {
        $hosts = new Host;

        // 遍历所有的搜索条件
        foreach (['name', 'module_id', 'status', 'user_id', 'price', 'managed_price', 'created_at', 'updated_at'] as $field) {
            if ($request->has($field)) {
                $hosts = $hosts->where($field, 'like', '%'.$request->input($field).'%');
            }
        }

        $hosts = $hosts->with(['user', 'module']);

        $hosts = $hosts->paginate(50)->withQueryString();

        return view('admin.hosts.index', compact('hosts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Host $host): View
    {
        return view('admin.hosts.edit', compact('host'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Host $host): RedirectResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:running,stopped,suspended,pending,locked,unavailable',
            'price' => 'sometimes|numeric',
            'managed_price' => 'nullable|numeric',
        ]);

        $host->update($request->all());

        return back()->with('success', '此主机已更新。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Host $host): RedirectResponse
    {
        $host->safeDelete();

        return redirect()->route('admin.hosts.index')->with('success', '正在排队删除此主机。');
    }

    public function updateOrDelete(Host $host): RedirectResponse
    {
        $host->updateOrDelete();

        return back()->with('success', '正在排队刷新此主机的状态。');
    }
}
