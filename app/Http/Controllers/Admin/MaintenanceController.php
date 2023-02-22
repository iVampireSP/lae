<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $maintenances = (new Maintenance)->orderByStartAt()->get();

        return view('admin.maintenances.index', compact('maintenances'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'content' => 'nullable|string',
            'module_id' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
        ]);

        (new Maintenance())->create($request->all());

        return redirect()->route('admin.maintenances.index')->with('success', '维护信息已创建。');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $modules = (new Module)->all();

        return view('admin.maintenances.create', compact('modules'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Maintenance $maintenance): View
    {
        $modules = (new Module)->all();

        return view('admin.maintenances.edit', compact('maintenance', 'modules'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Maintenance $maintenance): RedirectResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'module_id' => 'nullable|string|max:255',
            'start_at' => 'nullable|date',
            'end_at' => 'nullable|date',
        ]);

        $maintenance->update($request->all());

        return redirect()->route('admin.maintenances.index')->with('success', '维护信息已更新。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Maintenance $maintenance): RedirectResponse
    {
        $maintenance->delete();

        return redirect()->route('admin.maintenances.index')->with('success', '维护信息已删除。');
    }
}
