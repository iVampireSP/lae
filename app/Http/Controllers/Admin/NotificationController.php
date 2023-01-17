<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\User\SendUserNotificationsJob;
use App\Models\Module;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     *
     * @return View
     */
    public function create(Request $request): View
    {
        $modules = Module::all();

        $users = $this->query($request)->paginate(20)->withQueryString();

        return view('admin.notifications.create', compact('modules', 'users'));
    }

    public function query(Request|array $request): User|CachedBuilder|Builder
    {
        if ($request instanceof Request) {
            $request = $request->all();
        }

        if (!empty($request['user_id'])) {
            $users = (new User)->where('id', $request['user_id']);
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
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'user_id' => 'nullable',
            'module_id' => 'nullable',
            'user' => 'nullable',
            'send_mail' => 'boolean',
        ]);

        // send mail 是 checkbox，值为 1
        $send_mail = $request->has('send_mail');

        dispatch(new SendUserNotificationsJob($request->toArray(), $request->input('title'), $request->input('content'), $send_mail));

        return back()->with('success', '通知发送成功。')->withInput();
    }
}
