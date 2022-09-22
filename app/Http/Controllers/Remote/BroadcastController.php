<?php

namespace App\Http\Controllers\Remote;

use App\Models\Host;
use App\Models\User;
use App\Events\UserEvent;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BroadcastController extends Controller
{
    //

    public function broadcast_to_user(Request $request, User $user)
    {
        $this->validate($request, $this->rules());

        broadcast(new UserEvent($user->id, 'modules.users.event', [
            'user' => $user,
            'message' => $request->message
        ]));

        return $this->created($request->message);

    }

    public function broadcast_to_host(Request $request, Host $host)
    {
        $this->validate($request, $this->rules());


        broadcast(new UserEvent($host->user_id, 'modules.hosts.event', [
            'host' => $host,
            'message' => $request->message
        ]));

        return $this->created($request->message);
    }

    private function rules() {
        return [
            'message' => 'required',
        ];
    }
}
