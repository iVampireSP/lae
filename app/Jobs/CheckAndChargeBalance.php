<?php

namespace App\Jobs;

use App\Exceptions\ChargeException;
use App\Models\Balance;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Yansongda\LaravelPay\Facades\Pay;
use Yansongda\Pay\Exception\InvalidResponseException;

class CheckAndChargeBalance extends Job
{
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        Balance::where('paid_at', null)->chunk(100, function ($balances) {
            foreach ($balances as $balance) {
                if (!$this->checkAndCharge($balance, true)) {
                    if (now()->diffInDays($balance->created_at) > 1) {
                        $balance->delete();
                    }
                }
            }
        });

        Balance::where('paid_at', null)->where('created_at', '<', now()->subDays(2))->delete();
    }

    public function checkAndCharge(Balance $balance, $check = false): bool
    {

        if ($check) {
            $alipay = Pay::alipay()->find(['out_trade_no' => $balance->order_id]);

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
        } catch (InvalidResponseException|ChargeException $e) {
            Log::error($e->getMessage());
            return false;
        }

        return true;
    }
}
