<?php

namespace App\Http\Controllers\Web;

use App\Exceptions\ChargeException;
use App\Http\Controllers\Controller;
use App\Models\Balance;
use App\Models\Module;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Yansongda\LaravelPay\Facades\Pay;

class BalanceController extends Controller
{
    public function index(Request $request): View
    {
        $balance = $request->user()->balance;

        $balances = Balance::thisUser()->latest()->paginate(100);

        return view('balances.index', compact('balance', 'balances'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'amount' => 'required|integer|min:0.1|max:10000',
            'payment' => 'required|in:wechat,alipay',
        ]);

        $balance = Balance::create([
            'user_id' => auth('web')->id(),
            'amount' => $request->input('amount'),
            'payment' => $request->input('payment'),
        ]);

        return redirect()->route('balances.show', compact('balance'));
    }

    /**
     * 显示充值页面和状态(ajax)
     */
    public function show(Request $request, Balance $balance)
    {

        if ($balance->isPaid()) {
            if ($request->ajax()) {
                return $this->success($balance);
            }

            return view('balances.process', compact('balance'));
        } else {
            if ($request->ajax()) {
                return $this->success($balance);
            }
        }

        if ($balance->isOverdue()) {
            if (now()->diffInDays($balance->created_at) > 1) {
                if ($request->ajax()) {
                    return $this->forbidden($balance);
                }

                return redirect()->route('index')->with('error', '订单已逾期。');
            }
        }

        $balance->load('user');

        $subject = config('app.display_name') . ' 充值';

        $order = [
            'out_trade_no' => $balance->order_id,
        ];

        $code = QrCode::size(150);

        if ($balance->payment === 'wechat') {
            $pay = $this->xunhu_wechat($balance, $subject);

            $qr_code = $code->generate($pay['url']);
        } else {
            $order['subject'] = $subject;
            $order['total_amount'] = $balance->amount;

            $pay = Pay::alipay()->web($order);

            return view('balances.alipay', ['html' => (string)$pay->getBody()]);
        }

        if (!isset($qr_code)) {
            return redirect()->route('index')->with('error', '支付方式错误。');
        }

        return view('balances.pay', compact('balance', 'qr_code'));
    }

    private
    function xunhu_wechat(
        Balance $balance, $subject = '支付'
    ) {
        $data = [
            'version' => '1.1',
            'lang' => 'zh-cn',
            'plugins' => config('app.name'),
            'appid' => config('pay.xunhu.app_id'),
            'trade_order_id' => $balance->order_id,
            'payment' => 'wechat',
            'type' => 'WAP',
            'wap_url' => config('app.url'),
            'wap_name' => config('app.display_name'),
            'total_fee' => $balance->amount,
            'title' => $subject,
            'time' => time(),
            'notify_url' => route('balances.notify', 'wechat'),
            'return_url' => route('balances.notify', 'wechat'),
            'callback_url' => route('balances.show', $balance),
            'modal' => null,
            'nonce_str' => str_shuffle(time()),
        ];

        $data['hash'] = $this->xunhu_hash($data);

        $response = Http::post(config('pay.xunhu.gateway'), $data);

        if (!$response->successful()) {
            return redirect()->route('index')->with('error', '支付网关错误。');
        }

        $response = $response->json();

        $hash = $this->xunhu_hash($response);

        if (!isset($response['hash']) || $response['hash'] !== $hash) {
            return redirect()->route('index')->with('error', '无法校验支付网关返回数据。');
        }

        return $response;
    }

    private
    function xunhu_hash(
        array $arr
    ) {
        ksort($arr);

        $pre = [];
        foreach ($arr as $key => $data) {
            if (is_null($data) || $data === '') {
                continue;
            }
            if ($key == 'hash') {
                continue;
            }
            $pre[$key] = stripslashes($data);
        }

        $arg = '';
        $qty = count($pre);
        $index = 0;

        foreach ($pre as $key => $val) {
            $arg .= "$key=$val";
            if ($index++ < ($qty - 1)) {
                $arg .= "&";
            }
        }

        return md5($arg . config('pay.xunhu.app_secret'));
    }

    /**
     * @throws ValidationException
     */
    public
    function notify(
        Request $request, $payment
    ): View|JsonResponse {
        $is_paid = false;

        if ($payment === 'alipay') {
            $out_trade_no = $request->input('out_trade_no');
        } else if ($payment === 'wechat') {
            $out_trade_no = $request->input('trade_order_id');
        } else {
            abort(400, '支付方式错误');
        }

        // 检测订单是否存在
        $balance = Balance::where('order_id', $out_trade_no)->with('user')->first();
        if (!$balance) {
            abort(404, '找不到订单。');
        }

        // 检测订单是否已支付
        if ($balance->paid_at !== null) {
            if ($request->ajax()) {
                return $this->success($balance);
            }

            return view('balances.process', compact('balance'));
        }

        // 处理验证
        if ($payment === 'wechat') {
            if (!($request->filled('hash') || $request->filled('trade_order_id'))) {
                return $this->error('参数错误。');
            }

            if ($request->filled('plugins') && $request->input('plugins') != config('app.name')) {
                return $this->error('插件不匹配。');
            }

            $hash = $this->xunhu_hash($request->toArray());
            if ($request->input('hash') != $hash) {
                Log::debug('hash error', $request->toArray());
            }

            if ($request->input('status') === 'OD') {
                $is_paid = true;
            }
        }

        if ($is_paid) {
            try {
                (new Transaction)->addAmount($balance->user_id, 'alipay', $balance->amount);

                $balance->update([
                    'paid_at' => now()
                ]);

            } catch (ChargeException $e) {
                abort(500, $e->getMessage());
            }
        }


        if ($request->ajax()) {
            return $this->success($balance);
        }

        return view('balances.process', compact('balance'));

    }

    /**
     * 获取交易记录
     *
     * @param mixed $request
     *
     * @return View
     */
    public
    function transactions(
        Request $request
    ): View {

        $modules = Module::all();

        $transactions = Transaction::where('user_id', auth()->id());

        if ($request->has('type')) {
            $transactions = $transactions->where('type', $request->type);
        }

        if ($request->has('payment')) {
            $transactions = $transactions->where('payment', $request->payment);
        }

        $transactions = $transactions->latest()->paginate(100)->withQueryString();

        return view('balances.transactions', compact('transactions', 'modules'));
    }
}
