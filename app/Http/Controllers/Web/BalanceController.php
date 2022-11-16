<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\Module;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Yansongda\LaravelPay\Facades\Pay;

class BalanceController extends Controller
{
    //

    public function index(Request $request): View
    {

        $transaction = new Transaction();

        $drops = $transaction->getDrops();

        $balance = $request->user()->balance;

        $balances = Balance::thisUser()->latest()->paginate(50);

        $drops_rate = config('drops.rate');

        return view('balances.index', compact('drops', 'balance', 'balances', 'drops_rate'));
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

        // $balances['pay_url'] = route('balances.balances.show', ['balances' => $balances['order_id']]);

        return redirect()->route('balances.balances.show', ['balances' => $balance->order_id]);
    }

    /**
     */
    public function show(Balance $balance)
    {
        if ($balance->paid_at !== null) {
            return $this->error('订单已支付');
        }

        if (now()->diffInDays($balance->created_at) > 1) {
            return $this->error('订单已失效');
        }

        $web = Pay::alipay()->web([
            'out_trade_no' => $balance->order_id,
            'total_amount' => $balance->amount,
            'subject' => config('app.display_name') . ' 充值',
        ]);

        return view('balances.pay', ['html' => (string)$web->getBody()]);
    }

    /**
     * @throws ValidationException
     */
    public function notify(Request $request): View|JsonResponse
    {
        $this->validate($request, [
            'out_trade_no' => 'required',
        ]);

        // 检测订单是否存在
        $balance = Balance::where('order_id', $request->out_trade_no)->with('user')->first();
        if (!$balance) {
            abort(404, '找不到订单。');
        }

        // 检测订单是否已支付
        if ($balance->paid_at !== null) {
            // return $this->success('订单已支付');
            return view('balances.process', compact('balance'));
        }

        // try {
        //     $data = Pay::alipay()->callback();
        // } catch (InvalidResponseException $e) {
        //     return $this->error('支付失败');
        // }

        // // 检测 out_trade_no 是否为商户系统中创建的订单号
        // if ($data->out_trade_no != $balances->order_id) {
        //     return $this->error('订单号不一致');
        // }

        // if ((int) $data->total_amount != (int) $balances->amount) {
        //     throw new ChargeException('金额不一致');
        // }

        // // 验证 商户
        // if ($data['app_id'] != config('balances.alipay.default.app_id')) {
        //     throw new ChargeException('商户不匹配');
        // }

        return view('balances.process');

        //
        // if ((new \App\Jobs\CheckAndChargeBalance())->checkAndCharge($balance, true)) {
        //     return view('pay_process');
        // } else {
        //     abort(500, '支付失败');
        // }
    }

    /**
     * 获取交易记录
     *
     * @param mixed $request
     *
     * @return View
     */
    public function transactions(Request $request): View
    {

        $modules = Module::all();

        $transactions = Transaction::where('user_id', auth()->id());

        if ($request->has('type')) {
            $transactions = $transactions->where('type', $request->type);
        }

        if ($request->has('payment')) {
            $transactions = $transactions->where('payment', $request->payment);
        }

        $transactions = $transactions->latest()->paginate(30);

        return view('balances.transactions', compact('transactions', 'modules'));
    }
}
