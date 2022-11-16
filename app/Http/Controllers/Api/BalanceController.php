<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ChargeException;
use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yansongda\LaravelPay\Facades\Pay;
use Yansongda\Pay\Exception\InvalidResponseException;
use function auth;
use function config;
use function now;

class BalanceController extends Controller
{
    //

    public function index(): JsonResponse
    {
        $balances = Balance::thisUser()->simplePaginate(30);
        return $this->success($balances);
    }

    public function checkAndCharge(Balance $balance, $check = false): JsonResponse|bool
    {

        if ($check) {
            $alipay = Pay::alipay()->find(['out_trade_no' => $balance->order_id,]);

            if ($alipay->trade_status !== 'TRADE_SUCCESS') {
                return false;
            }
        }

        if ($balance->paid_at !== null) {
            return true;
        }

        try {
            (new Transaction)->addAmount($balance->user_id, 'alipay', $balance->amount);

            $balance->update([
                'paid_at' => now()
            ]);
        } catch (InvalidResponseException $e) {
            Log::error($e->getMessage());
            return $this->error('无法验证支付结果。');
        } catch (ChargeException $e) {
            Log::error($e->getMessage());
            return $this->error('暂时无法处理充值。');

        }

        return true;
    }

    /**
     * 获取交易记录
     *
     * @param mixed $request
     *
     * @return JsonResponse
     */
    public function transactions(Request $request): JsonResponse
    {
        $transactions = Transaction::where('user_id', auth()->id());

        if ($request->has('type')) {
            $transactions = $transactions->where('type', $request->type);
        }

        if ($request->has('payment')) {
            $transactions = $transactions->where('payment', $request->payment);
        }

        $transactions = $transactions->latest()->simplePaginate(30);

        return $this->success($transactions);
    }

    /**
     * 获取 Drops
     *
     * @return JsonResponse
     */
    public function drops(): JsonResponse
    {
        $user_id = auth()->id();

        $resp = [
            'drops' => (new Transaction())->getDrops($user_id),
            'rate' => config('drops.rate'),
        ];

        return $this->success($resp);
    }
}
