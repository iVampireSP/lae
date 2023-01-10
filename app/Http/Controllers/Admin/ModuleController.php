<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\Module;
use App\Models\ModuleAllow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $module->id = $request->input('id');
        $module->name = $request->input('name');
        $module->api_token = $api_token;
        $module->url = $request->input('url');
        $module->status = $request->input('status');

        $module->save();

        return redirect()->route('admin.modules.edit', $module)->with('success', '模块创建成功, 请重置以获得 API Token。');

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

        $hosts = (new Host)->where('module_id', $module->id)->with('user')->latest()->paginate(50);

        return view('admin.modules.show', compact('module', 'years', 'hosts'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Module $module
     *
     * @return View
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
     * @return RedirectResponse
     */
    public function update(Request $request, Module $module): RedirectResponse
    {
        $request->validate($this->rules());

        if ($request->input('reset_api_token')) {
            $module->api_token = Str::random(60);
        }

        $module->id = $request->input('id');
        $module->name = $request->input('name');
        $module->url = $request->input('url');
        $module->status = $request->input('status');

        $module->save();

        $text = '模块更新成功';

        if ($request->input('reset_api_token')) {
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
        $module->delete();

        return redirect()->route('admin.modules.index')->with('success', '模块已删除。');
    }

    public function allows(Module $module): View
    {
        $allows = (new ModuleAllow)->where('module_id', $module->id)->with('allowed_module')->paginate(50);

        return view('admin.modules.allows', compact('module', 'allows'));
    }

    public function allows_store(Request $request, Module $module): RedirectResponse
    {
        $request->validate([
            'allowed_module_id' => 'required|string|max:255|exists:modules,id',
        ]);

        (new ModuleAllow)->where('module_id', $module->id)->where('allowed_module_id', $request->input('allow_module_id'))->firstOrCreate([
            'module_id' => $module->id,
            'allowed_module_id' => $request->get('allowed_module_id'),
        ]);

        return back()->with('success', '已信任该模块。');
    }


    // fast login

    public function allows_destroy(Module $module, ModuleAllow $allow): RedirectResponse
    {
        $allow->delete();

        return redirect()->route('admin.modules.allows', $module)->with('success', '取消信任完成。');
    }

    public function fast_login(Module $module): View|RedirectResponse
    {
        $resp = $module->baseRequest('post', 'fast-login', []);

        if ($resp['success']) {
            $resp = $resp['json'];
            return view('admin.modules.login', compact('module', 'resp'));
        } else {
            return redirect()->route('admin.modules.show', $module)->with('error', '快速登录失败，可能是模块不支持。');
        }
    }
}
