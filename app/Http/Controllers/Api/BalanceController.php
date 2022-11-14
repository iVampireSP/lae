<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\ChargeException;
use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Yansongda\LaravelPay\Facades\Pay;
use Yansongda\Pay\Exception\InvalidResponseException;
use function auth;
use function config;
use function now;
use function route;
use function view;

class BalanceController extends Controller
{
    //

    public function index(Request $request)
    {
        $balances = Balance::thisUser()->simplePaginate(30);
        return $this->success($balances);
    }

    public function store(Request $request)
    {
        // 充值
        $this->validate($request, [
            'amount' => 'required|integer|min:1|max:10000',
        ]);

        $user = $request->user();

        $balance = new Balance();


        $data = [
            'user_id' => $user->id,
            'amount' => $request->amount,
            'payment' => 'alipay',
        ];

        // if local
        // if (env('APP_ENV') == 'local') {
        //     $data['payment'] = null;
        //     $data['paid_at'] = now();
        // }


        $balance = $balance->create($data);

        // 生成 18 位订单号
        $order_id = date('YmdHis') . $balance->id . rand(1000, 9999);
        $balance->order_id = $order_id;

        $balance->save();

        $balance = $balance->toArray();
        $balance['pay_url'] = route('balances.pay.show', ['balance' => $balance['order_id']]);

        return $this->success($balance);
    }


    public function show(Balance $balance)
    {
        if ($balance->paid_at !== null) {
            return $this->error('订单已支付');
        }

        if (now()->diffInDays($balance->created_at) > 1) {
            return $this->error('订单已失效');
        }

        $pay = Pay::alipay()->web([
            'out_trade_no' => $balance->order_id,
            'total_amount' => $balance->amount,
            'subject' => config('app.display_name') . ' 充值',
        ]);

        return $pay;
    }

    public function notify(Request $request)
    {
        $this->validate($request, [
            'out_trade_no' => 'required',
        ]);

        // 检测订单是否存在
        $balance = Balance::where('order_id', $request->out_trade_no)->with('user')->first();
        if (!$balance) {
            return $this->notFound('找不到对应的订单');
        }

        // 检测订单是否已支付
        if ($balance->paid_at !== null) {
            // return $this->success('订单已支付');
            return view('pay_process');
        }

        // try {
        //     $data = Pay::alipay()->callback();
        // } catch (InvalidResponseException $e) {
        //     return $this->error('支付失败');
        // }

        // // 检测 out_trade_no 是否为商户系统中创建的订单号
        // if ($data->out_trade_no != $balance->order_id) {
        //     return $this->error('订单号不一致');
        // }

        // if ((int) $data->total_amount != (int) $balance->amount) {
        //     throw new ChargeException('金额不一致');
        // }

        // // 验证 商户
        // if ($data['app_id'] != config('pay.alipay.default.app_id')) {
        //     throw new ChargeException('商户不匹配');
        // }

        if ($this->checkAndCharge($balance, true)) {
            return view('pay_process');
        } else {
            return $this->error('支付失败');
        }
    }

    public function checkAndCharge(Balance $balance, $check = false)
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
        } catch (InvalidResponseException) {
            return $this->error('无法验证支付结果。');
        }

        return true;
    }

    /**
     * 获取交易记录
     *
     * @param  mixed $request
     * @return void
     */
    public function transactions(Request $request)
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
     * @return void
     */
    public function drops()
    {
        $user_id = auth()->id();

        $resp = [
            'drops' => (new Transaction())->getDrops($user_id),
            'rate' => config('drops.rate'),
        ];

        return $this->success($resp);
    }
}
