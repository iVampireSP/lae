<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\User\Balance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Alipay\EasySDK\Kernel\Util\ResponseChecker;
use Alipay\EasySDK\Kernel\Factory as AlipayFactory;

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
        $request->validate([
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
        if (env('APP_ENV') == 'local') {
            $data['payment'] = null;
            $data['paid_at'] = now();
        }


        $balance = $balance->create($data);

        if (env('APP_ENV') == 'local') {
            $user->increment('balance', $request->amount);
            return $this->success($balance);
        }



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
        // dd(AlipayFactory::payment()->common()->query('20220901070430102316'));
        // dd(route(''));
        if ($balance->paid_at !== null) {
            return $this->error('订单已支付');
        }

        try {
            $result = AlipayFactory::payment()->page()->pay("支付", $balance->order_id, $balance->amount, route('balances.notify'));

            $responseChecker = new ResponseChecker();

            // dd($result);

            if ($responseChecker->success($result)) {
                $html = $result->body;
                return view('pay', compact('html'));
            }
        } catch (Exception $e) {
            dd($e->getMessage());
            echo "调用失败，" . $e->getMessage() . PHP_EOL;;
        }


        return $this->success($balance);
    }

    public function notify(Request $request)
    {
        // 检测订单是否存在
        $balance = Balance::where('order_id', $request->out_trade_no)->with('user')->first();
        if (!$balance) {
            return $this->notFound('balance not found');
        }

        // 检测订单是否已支付
        if ($balance->paid_at !== null) {
            return $this->success('订单已支付');
        }

        $trade = AlipayFactory::payment()->common()->query($request->out_trade_no);

        if ($trade->code == "10000" && $trade->tradeStatus == "TRADE_SUCCESS") {
            $balance->paid_at = now();
            $balance->save();


            DB::beginTransaction();
            try {
                $balance->user->increment('balance', $trade->totalAmount);
                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                AlipayFactory::payment()->common()->refund($request->out_trade_no, $trade->totalAmount);
                return $this->error($e->getMessage());
            }

            return $this->success('订单支付成功');
        } else {
            return $this->error('订单支付失败');
        }


    }

    // // 转换为 drops
    // public function transfer($amount = 1)
    // {
    //     $balance = auth('sanctum')->user();
    //     $balance->decrement('amount', $request->amount);
    //     return $this->success($balance);
    // }


}
