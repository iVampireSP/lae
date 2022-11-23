<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\Module;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ModuleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Module $module
     *
     * @return View
     */
    public function index(Module $module): View
    {
        $modules = $module->paginate(50);

        return view('admin.modules.index', compact('modules'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        //

        return view('admin.modules.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules());

        $api_token = Str::random(60);

        $module = new Module();

        $module->id = $request->id;
        $module->name = $request->name;
        $module->api_token = $api_token;
        $module->url = $request->url;
        $module->status = $request->status;

        $module->save();

        return redirect()->route('admin.modules.edit', $module)->with('success', '模块创建成功, 请重置以获得 API Token。');

    }

    /**
     * Display the specified resource.
     *
     * @param Module $module
     *
     * @return View
     */
    public function show(Module $module): View
    {
        $years = $module->calculate();

        $hosts = Host::where('module_id', $module->id)->latest()->paginate(50);

        return view('admin.modules.show', compact('module', 'years', 'hosts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Module $module
     *
     * @return Response
     */
    public function edit(Module $module): View
    {
        //

        return view('admin.modules.edit', compact('module'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Module  $module
     *
     * @return Response
     */
    public function update(Request $request, Module $module): RedirectResponse
    {
        //

        $request->validate($this->rules());


        if ($request->reset_api_token) {
            $module->api_token = Str::random(60);
        }

        $module->id = $request->id;
        $module->name = $request->name;
        $module->url = $request->url;
        $module->status = $request->status;

        $module->save();

        $text = '模块更新成功';

        if ($request->reset_api_token) {
            $text .= ', API Token 为 ' . $module->api_token . '。';
        } else {
            $text .= '。';
        }

        return redirect()->route('admin.modules.edit', $module->id)->with('success', $text);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Module $module
     *
     * @return RedirectResponse
     */
    public function destroy(Module $module): RedirectResponse
    {
        //
        $module->delete();

        return redirect()->route('admin.modules.index')->with('success', '模块已删除。');
    }

    private function rules(): array
    {
        return [
            'id' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'url' => 'required|url',
            'status' => 'required|string|in:up,down,maintenance',
        ];
    }

}
