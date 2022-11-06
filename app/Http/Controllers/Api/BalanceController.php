<?php

namespace App\Http\Controllers\Api;

use Exception;
use function app;
use function env;
use function now;
use function auth;
use function view;
use function route;
use function config;
use App\Models\Balance;
use function storage_path;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Exceptions\ChargeException;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Yansongda\LaravelPay\Facades\Pay;
use Illuminate\Support\Facades\Storage;
use Yansongda\Pay\Exception\InvalidResponseException;

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

        $pay = Pay::alipay()->web([
            'out_trade_no' => 'lae-' . time(),
            'total_amount' => $request->amount,
            'subject' => '在莱云上充值 ' . $request->amount . ' 元',
        ]);


        return $pay;


        // if local
        // if (env('APP_ENV') == 'local') {
        //     $data['payment'] = null;
        //     $data['paid_at'] = now();
        // }


        $balance = $balance->create($data);

        // if (env('APP_ENV') == 'local') {
        //     $user->increment('balance', $request->amount);
        //     return $this->success($balance);
        // }



        // 生成 18 位订单号
        $order_id = date('YmdHis') . $balance->id . rand(1000, 9999);
        $balance->order_id = $order_id;

        $balance->save();

        $balance = $balance->toArray();
        $balance['pay_url'] = route('balances.pay.show', ['balance' => $balance['order_id']]);

        return $this->success($balance);
    }


    public function show(Request $request, Balance $balance)
    {
        if ($balance->paid_at !== null) {
            return $this->error('订单已支付');
        }


        if (now()->diffInDays($balance->created_at) > 1) {
            return $this->error('订单已失效');
        }

        try {

            return;
            // if ($responseChecker->success($result)) {
            //     $html = $result->body;
            //     return view('pay', compact('html'));
            // }
        } catch (Exception $e) {
            Log::error($e);
            echo "调用失败，" . $e->getMessage() . PHP_EOL;;
        }


        return $this->success($balance);
    }

    public function return(Request $request)
    {
        $this->validate($request, [
            'out_trade_no' => 'required',
        ]);

        $alipay = Pay::alipay();

        $data = $alipay->callback();

        Log::debug($data);

        dd($data);

        return;
        // 检测订单是否存在
        // $balance = Balance::where('order_id', $request->out_trade_no)->with('user')->first();
        // if (!$balance) {
        //     return $this->notFound('balance not found');
        // }

        // 检测订单是否已支付
        if ($balance->paid_at !== null) {
            return $this->success('订单已支付');
        }

        if ($this->checkAndCharge($balance)) {
            return view('pay_success');
        } else {
            return view('pay_error');
        }
    }

    public function notify(Request $request)
    {
        $this->validate($request, [
            'out_trade_no' => 'required',
        ]);

        // 检测订单是否存在
        $balance = Balance::where('order_id', $request->out_trade_no)->with('user')->first();
        if (!$balance) {
            return $this->notFound('balance not found');
        }

        // 检测订单是否已支付
        if ($balance->paid_at !== null) {
            return $this->success('订单已支付');
        }

        if ($this->checkAndCharge($balance)) {
            return $this->success();
        } else {
            return $this->error();
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

        try {

            // 请自行对 trade_status 进行判断及其它逻辑进行判断，在支付宝的业务通知中，只有交易通知状态为 TRADE_SUCCESS 或 TRADE_FINISHED 时，支付宝才会认定为买家付款成功。
            // 1、商户需要验证该通知数据中的out_trade_no是否为商户系统中创建的订单号；
            // 2、判断total_amount是否确实为该订单的实际金额（即商户订单创建时的金额）；
            // 3、校验通知中的seller_id（或者seller_email) 是否为out_trade_no这笔单据的对应的操作方（有的时候，一个商户可能有多个seller_id/seller_email）；
            // 4、验证app_id是否为该商户本身。
            // 5、其它业务逻辑情况

            // 验证 商户
            // if ($data['app_id'] != config('pay.alipay.app_id')) {
            //     throw new ChargeException('商户不匹配');
            // }

            // if ((int) $data->total_amount != (int) $balance->amount) {
            //     throw new ChargeException('金额不一致');
            // }

            $balance->update([
                'paid_at' => now()
            ]);

            (new Transaction)->addAmount($balance->user_id, 'alipay', $data->totalAmount);
        } catch (InvalidResponseException) {
            return $this->error('无法验证支付结果');
        }

        return $alipay->success();
    }

    // // 转换为 drops
    // public function transfer($amount = 1)
    // {
    //     $balance = auth()->user();
    //     $balance->decrement('amount', $request->amount);
    //     return $this->success($balance);
    // }


    public function transactions(Request $request)
    {
        $transactions = Transaction::thisUser();


        if ($request->has('type')) {
            $transactions = $transactions->where('type', $request->type);
        }

        if ($request->has('payment')) {
            $transactions = $transactions->where('payment', $request->payment);
        }

        $transactions = $transactions->latest()->simplePaginate(30);

        return $this->success($transactions);
    }



    public function drops()
    {
        // $month = now()->month;

        $user_id = auth()->id();

        // $cache_key = 'user_' . $user_id . '_month_' . $month . '_drops';

        $transactions = new Transaction();

        $resp = [
            'drops' => $transactions->getDrops($user_id),
            // 'monthly_usages' => (double) Cache::get($cache_key, 0),
            'rate' => config('drops.rate'),
        ];

        return $this->success($resp);
    }


    // private function alipayOptions()
    // {
    //     $options = new AlipayConfig();
    //     $options->protocol = 'https';

    //     // if local
    //     if (app()->environment() == 'local') {
    //         $options->gatewayHost = 'openapi.alipaydev.com';
    //     } else {
    //         $options->gatewayHost = 'openapi.alipay.com';
    //     }

    //     $options->signType = 'RSA2';

    //     $options->appId = config('payment.alipay.app_id');

    //     // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
    //     $options->merchantPrivateKey = trim(Storage::get('alipayAppPriv.key'));

    //     $options->alipayCertPath = storage_path('app/alipayCertPublicKey_RSA2.crt');
    //     $options->alipayRootCertPath = storage_path('app/alipayRootCert.crt');
    //     $options->merchantCertPath = storage_path('app/appCertPublicKey.crt');

    //     $options->notifyUrl = route('balances.notify');


    //     return $options;
    // }
}
