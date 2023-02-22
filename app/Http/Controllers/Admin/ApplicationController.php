<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $applications = (new Application)->paginate(100);

        return view('admin.applications.index', compact('applications'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): View
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'api_token' => 'required|unique:applications,api_token',
        ]);

        $application = (new Application)->create($request->all());

        return view('admin.applications.edit', compact('application'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('admin.applications.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(Application $application): RedirectResponse
    {
        return redirect()->route('admin.applications.edit', $application);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Application $application): View
    {
        return view('admin.applications.edit', compact('application'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Application $application): RedirectResponse
    {
        $request->validate([
            'name' => 'required',
            'description' => 'required',
            'api_token' => 'required|unique:applications,api_token,'.$application->id,
        ]);

        $application->update($request->all());

        return back()->with('success', '应用程序已更新。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Application $application): RedirectResponse
    {
        $application->delete();

        return redirect()->route('admin.applications.index')->with('success', '应用程序已删除。');
    }
}
