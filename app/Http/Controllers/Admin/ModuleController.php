<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
     * @return Response
     */
    public function index(Module $module)
    {
        $modules = $module->paginate(100);

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
     * @param \Illuminate\Http\Request $request
     *
     * @return Response
     */
    public function store(Request $request)
    {
        //

        $request->validate($this->rules());


        $api_token = Str::random(60);

        $module = new Module();

        $module->id = $request->id;
        $module->name = $request->name;
        $module->api_token = $api_token;
        $module->url = $request->url;
        $module->save();

        return redirect()->route('admin.modules.index')->with('success', '模块创建成功, 请重置以获得 API Token。');

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Module $module
     *
     * @return View
     */
    public function show(Module $module): View
    {
        $years = $module->calculate();

        return view('admin.modules.show', compact('module', 'years'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Module $module
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
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Module       $module
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
     * @param \App\Models\Module $module
     *
     * @return Response
     */
    public function destroy(Module $module)
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
        ];
    }
}
