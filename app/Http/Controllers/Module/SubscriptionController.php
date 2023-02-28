<?php

namespace App\Http\Controllers\Module;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, User $user): JsonResponse
    {
        $subscriptions = $user->subscriptions()->where('module_id', $request->user('module')->id);

        if ($request->filled('status')) {
            $subscriptions->where('status', $request->input('status'));
        }

        if ($request->filled('plan_id')) {
            $subscriptions->where('plan_id', $request->input('plan_id'));
        }

        $subscriptions = $subscriptions->paginate();

        return $this->success($subscriptions);
    }

    /**
     * 向用户发送订阅请求。
     */
    public function store(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'plan_id' => 'required|string|max:255',
            'configuration' => 'nullable|json',
            'price' => 'required|numeric|min:0',
            'trial_ends_at' => 'nullable|date|after:now',
        ]);

        $subscription = $user->subscriptions()->create([
            'name' => $request->input('name'),
            'plan_id' => $request->input('plan_id'),
            'configuration' => $request->input('configuration'),
            'price' => $request->input('price'),
            'trial_ends_at' => $request->input('trial_ends_at'),
            'module_id' => $request->user('module')->id,
        ]);

        $subscription->url = route('subscriptions.show', $subscription);

        return $this->success($subscription);
    }

    /**
     * 展示订阅详情。
     */
    public function show(Subscription $subscription): JsonResponse
    {
        return $this->success($subscription);
    }

    /**
     * 更新订阅。
     */
    public function update(Request $request, User $user, Subscription $subscription): JsonResponse
    {
        unset($user);

        if ($subscription->status === 'active') {
            return $this->badRequest('此订阅已经成立，无法修改。');
        }

        $subscription->update($request->only([
            'name',
            'plan_id',
            'configuration',
            'price',
            'trial_ends_at',
        ]));

        return $this->success($subscription);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Subscription $subscription): JsonResponse
    {
        unset($user);

        $subscription->safeDelete();

        return $this->deleted();
    }

    public function by_plan_id(User $user, Subscription $subscription): JsonResponse
    {
        unset($user);

        return $this->success($subscription);
    }
}
