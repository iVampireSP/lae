<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Host;
use App\Models\User;
use function auth;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// use App\Models\User;

class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Paginator
    {
        return (new Host)->where('module_id', $request->user('module')->id)->simplePaginate(100);
    }

    /**
     * Store a newly created resource in storage.
     *
     *
     * @throws ValidationException
     */
    public function store(Request $request): Response|JsonResponse
    {
        // 存储计费项目
        $this->validate($request, [
            'status' => 'required|in:draft,running,stopped,error,suspended,pending',
            'price' => 'required|numeric',
            'user_id' => 'required|integer|exists:users,id',
            'managed_price' => 'nullable|numeric',
            'billing_cycle' => 'nullable|in:hourly,monthly',
            'trial_ends_at' => 'nullable|date|after:now',
            'configuration' => 'nullable|array',
        ]);

        $user = (new User)->findOrFail($request->input('user_id'));

        if ($request->input('price') > 0) {
            if ($request->billing_cycle === 'hourly') {
                if (! $user->hasBalance(1)) {
                    return $this->error('此用户余额不足，无法开设计费项目。');
                }
            } else {
                if (! $user->hasBalance($request->input('managed_price', $request->input('price')))) {
                    return $this->error('此用户余额不足，无法开计月费项目。');
                }
            }
        }

        // 如果没有 name，则随机
        $name = $request->input('name', Str::random(10));

        $data = [
            'name' => $name,
            'user_id' => $user->id,
            'module_id' => auth('module')->id(),
            'price' => $request->input('price'),
            'managed_price' => $request->input('managed_price'),
            'status' => $request->input('status'),
            'billing_cycle' => $request->input('billing_cycle', 'hourly'),
            'trial_ends_at' => $request->input('trial_ends_at'),
        ];

        $host = (new Host)->create($data);

        $host['host_id'] = $host->id;

        return $this->created($host);
    }

    /**
     * Display the specified resource.
     */
    public function show(Host $host): JsonResponse
    {
        return $this->success($host);
    }

    /**
     * Update the specified resource in storage.
     *
     *
     * @throws ValidationException
     */
    public function update(Request $request, Host $host): JsonResponse
    {
        $this->validate($request, [
            'status' => 'sometimes|in:running,stopped,error,suspended,pending',
            'price' => 'sometimes|nullable|numeric',
            'managed_price' => 'sometimes|numeric|nullable',
            'configuration' => 'nullable|array',
            'trial_ends_at' => 'nullable|date|after:now',
        ]);

        $update = $request->only([
            'name',
            'status',
            'price',
            'managed_price',
            'billing_cycle',
            'trial_ends_at',
            'configuration',
        ]);

        $host->update($update);

        return $this->updated($host);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($host): JsonResponse
    {
        // if host not instance of HostJob
        if (! $host instanceof Host) {
            $host = (new Host)->findOrFail($host);
        }

        $host?->delete();

        return $this->deleted();
    }

    public function cost(Request $request, Host $host): JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required|numeric|min:0.0001',
            'description' => 'nullable|string:max:255',
        ]);

        $host->cost($request->input('amount'), false, $request->input('description'));

        return $this->noContent();
    }
}
