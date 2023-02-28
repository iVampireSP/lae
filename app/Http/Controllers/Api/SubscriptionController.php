<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $subscriptions = Subscription::thisUser()->with('module')->orderBy('status')->paginate();

        return $this->success($subscriptions);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription): JsonResponse
    {
        $subscription->load('module');

        return $this->success($subscription);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Subscription $subscription): JsonResponse
    {
        $request->validate([
            'cancel_at_period_end' => 'nullable|boolean',
            'status' => 'nullable|in:active',
        ]);

        if ($request->filled('cancel_at_period_end')) {
            $subscription->update([
                'cancel_at_period_end' => $request->cancel_at_period_end,
            ]);
        }

        if ($request->filled('status') && $request->input('status') === 'active') {
            if (! $subscription->active()) {
                return $this->badRequest('无法激活此订阅。');
            }
        }

        return $this->success($subscription);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Subscription $subscription): JsonResponse
    {
        $subscription->safeDelete();

        return $this->deleted();
    }
}
