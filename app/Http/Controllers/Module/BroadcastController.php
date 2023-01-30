<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\WebNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function broadcast_to_user(Request $request, User $user): JsonResponse
    {
        $this->validate($request, $this->rules());

        $user->notify(new WebNotification($request->all(), $request->input('event')));

        return $this->created('message sent.');
    }

    private function rules(): array
    {
        return [
            'message' => 'required|string|max:255',
            'event' => 'required|alpha',
        ];
    }

    // public function broadcast_to_host(Request $request, Host $host): JsonResponse
    // {
    //     $this->validate($request, $this->rules());
    //
    //     broadcast(new Users($host->user, 'modules.hosts.event', [
    //         'host' => $host,
    //         'message' => $request->input('message')
    //     ]));
    //
    //     return $this->created($request->input('message'));
    // }
}
