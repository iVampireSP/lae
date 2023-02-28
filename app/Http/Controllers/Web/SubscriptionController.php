<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function index(Subscription $subscription)
    {
        $subscriptions = $subscription->thisUser()->with('module')->orderBy('status')->get();

        return view('subscription.index', compact('subscriptions'));
    }

    public function update(Request $request, Subscription $subscription): JsonResponse|RedirectResponse
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
                if ($request->ajax()) {
                    return $this->badRequest('无法激活此订阅。');
                }

                return back()->withErrors('无法激活此订阅。');
            }
        }

        if ($request->ajax()) {
            return $this->success($subscription);
        }

        return back();
    }

    public function show(Subscription $subscription)
    {
        $subscription->load('module');

        return view('subscription.show', compact('subscription'));
    }

    public function destroy(Subscription $subscription)
    {
        $subscription->safeDelete();

        return back();
    }
}
