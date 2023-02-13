<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index(): View
    {
        $user_groups = (new UserGroup)->paginate(10);

        return view('admin.user-groups.index', compact('user_groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate($this->rules());

        $user_group = (new UserGroup)->create($request->all());

        return redirect()->route('admin.user-groups.edit', $user_group)->with('success', '用户组新建成功。');
    }

    private function rules(): array
    {
        return [
            'name' => 'required|string',
            'color' => 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            'discount' => 'required|numeric|min:0|max:100',
            'exempt' => 'required|boolean',
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('admin.user-groups.create');
    }

    /**
     * Display the specified resource.
     *
     * @param  UserGroup  $user_group
     * @return View
     */
    public function show(UserGroup $user_group): View
    {
        $users = (new User)->where('user_group_id', $user_group->id)->paginate(100);

        return view('admin.user-groups.show', compact('user_group', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  UserGroup  $user_group
     * @return View
     */
    public function edit(UserGroup $user_group): View
    {
        return view('admin.user-groups.edit', compact('user_group'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  UserGroup  $user_group
     * @return RedirectResponse
     */
    public function update(Request $request, UserGroup $user_group): RedirectResponse
    {
        $request->validate($this->rules());

        $user_group->update($request->all());

        return back()->with('success', '用户组更新成功。');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  UserGroup  $user_group
     * @return RedirectResponse
     */
    public function destroy(UserGroup $user_group): RedirectResponse
    {
        $user_group->delete();

        return redirect()->route('admin.user-groups.index')->with('success', '用户组删除成功。');
    }
}
