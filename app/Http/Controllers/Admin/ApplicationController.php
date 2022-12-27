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
     *
     * @return View
     */
    public function index()
    {
        //

        $applications = Application::paginate(100);

        return view('admin.applications.index', compact('applications'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return View
     */
    public function store(Request $request)
    {
        //

        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'api_token' => 'required|unique:applications,api_token',
        ]);

        $application = Application::create($request->all());

        return view('admin.applications.edit', compact('application'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create()
    {
        //

        return view('admin.applications.create');
    }

    /**
     * Display the specified resource.
     *
     * @param Application $application
     *
     * @return RedirectResponse
     */
    public function show(Application $application)
    {
        //

        return redirect()->route('admin.applications.edit', $application);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Application $application
     *
     * @return View
     */
    public function edit(Application $application)
    {
        //

        return view('admin.applications.edit', compact('application'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request     $request
     * @param Application $application
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Application $application)
    {
        //

        $this->validate($request, [
            'name' => 'required',
            'description' => 'required',
            'api_token' => 'required|unique:applications,api_token,' . $application->id,
        ]);

        $application->update($request->all());

        return back()->with('success', '应用程序已更新。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Application $application
     *
     * @return RedirectResponse
     */
    public function destroy(Application $application)
    {
        //

        $application->delete();

        return redirect()->route('admin.applications.index')->with('success', '应用程序已删除。');
    }
}
