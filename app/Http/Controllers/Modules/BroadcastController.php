<?php

namespace App\Http\Controllers\Modules;

use App\Events\Users;
use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function broadcast_to_user(Request $request, User $user): JsonResponse
    {
        $this->validate($request, $this->rules());

        $type = 'modules.users.event';

        if ($request->filled('type')) {
            $type .= '.' . $request->input('type');
        } else {
            $type .= '.message';
        }

        broadcast(new Users($user, $type, [
            'user' => $user,
            'message' => $request->input('message')
        ]));

        return $this->created($request);
    }

    private function rules(): array
    {
        return [
            'message' => 'required',
        ];
    }

    public function broadcast_to_host(Request $request, Host $host): JsonResponse
    {
        $this->validate($request, $this->rules());

        broadcast(new Users($host->user, 'modules.hosts.event', [
            'host' => $host,
            'message' => $request->input('message')
        ]));

        return $this->created($request->input('message'));
    }
}
