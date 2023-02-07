<?php

namespace App\Http\Controllers\Module;

use App\Exceptions\EmqxSupportException;
use App\Http\Controllers\Controller;
use App\Jobs\Support\EMQXKickClientJob;
use App\Support\EmqxSupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $emqx = new EmqxSupport();

        try {
            $clients = $emqx->pagination([
                'like_username' => $request->user('module')->id . '.',
            ]);
        } catch (EmqxSupportException $e) {
            Log::error('emqx connect failed.', [$e]);

            return $this->error('unable connectto emqx');
        }

        return $this->success($clients);
    }

    public function destroy(Request $request, $client_id = null): JsonResponse
    {
        $request->validate([
            'client_id' => 'nullable|string|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        $emqx = new EmqxSupport();

        $client_id = $client_id ?? $request->input('client_id');

        if ($client_id) {
            try {
                $client = $emqx->client($client_id);
            } catch (EmqxSupportException) {
                return $this->failed('client not found');
            }

            $module_name = explode('.', $client['username'])[0];

            if ($request->user('module')->id !== $module_name) {
                return $this->failed('client not found');
            }

            $emqx->kickClient($client_id);

            return $this->deleted();
        }

        if ($request->filled('name')) {
            $name = $request->input('name');
            $module_name = explode('.', $name)[0];

            if ($request->user('module')->id !== $module_name) {
                return $this->failed('client not found');
            }

            $this->dispatch(new EMQXKickClientJob(null, $name, false));

            return $this->deleted();
        }

        return $this->failed('missing client_id or name');
    }
}
