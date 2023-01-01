<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendCommonNotificationsJob;
use App\Models\Module;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(Request $request)
    {
        $modules = Module::all();

        $users = $this->query($request)->paginate(20)->withQueryString();

        return view('admin.notifications.create', compact('modules', 'users'));
    }

    public function query(Request|array $request): User|CachedBuilder
    {
        if ($request instanceof Request) {
            $request = $request->all();
        }

        if (!empty($request['user_id'])) {
            $users = User::where('id', $request['user_id']);
        } else {
            $users = User::query();

            if (!empty($request['user'])) {
                $user = $request['user'];

                if ($user == 'active') {
                    // 寻找有 host 的用户
                    $users = $users->whereHas('hosts');
                } else if ($user == 'normal') {
                    $users = $users->whereNull('banned_at');
                } else if ($user == 'banned') {
                    $users = $users->whereNotNull('banned_at');
                }
            }
        }

        if (!empty($request['module_id'])) {
            // 从 hosts 中找到 module_id，然后找到拥有此 host 的用户
            $users = $users->whereHas('hosts', function ($query) use ($request) {
                $query->where('module_id', $request['module_id']);
            });
        }

        return $users;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'user_id' => 'nullable',
            'module_id' => 'nullable',
            'user' => 'nullable',
        ]);

        dispatch(new SendCommonNotificationsJob($request->toArray(), $request->input('title'), $request->input('content')));

        return back()->with('success', '通知发送成功。')->withInput();
    }
}
