<?php

namespace App\Http\Controllers\Modules;

use App\Events\Users;
use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function broadcast_to_user(Request $request, User $user)
    {
        $this->validate($request, $this->rules());

        broadcast(new Users($user->id, 'modules.users.event', [
            'user' => $user,
            'message' => $request->input('message')
        ]));

        return $this->created($request->input('message'));
    }

    private function rules()
    {
        return [
            'message' => 'required',
        ];
    }

    public function broadcast_to_host(Request $request, Host $host)
    {
        $this->validate($request, $this->rules());

        broadcast(new Users($host->user, 'modules.hosts.event', [
            'host' => $host,
            'message' => $request->input('message')
        ]));

        return $this->created($request->input('message'));
    }
}
