<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Balance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $balances = (new Balance)->thisUser()->paginate(100);

        return $this->success($balances);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $this->validate($request, [
            'amount' => 'required|integer|min:0.1|max:10000',
            'payment' => 'required|in:wechat,alipay',
        ]);

        $balance = (new Balance)->create([
            'user_id' => auth('sanctum')->id(),
            'amount' => $request->input('amount'),
            'payment' => $request->input('payment'),
        ]);

        $url = route('balances.show', compact('balance'));
        $balance->url = $url;

        return $this->success($balance);
    }

    /**
     * Display the specified resource.
     *
     * @param Balance $balance
     *
     * @return JsonResponse
     */
    public function show(Balance $balance): JsonResponse
    {
        if ($balance->canPay()) {
            $url = route('balances.show', compact('balance'));
            $balance->url = $url;

            return $this->success($balance);
        } else {
            return $this->badRequest('无法支付。');
        }
    }
}
