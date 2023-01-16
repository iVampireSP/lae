<?php

namespace App\Jobs\User;

use App\Jobs\Job;
use App\Models\Balance;
use Illuminate\Contracts\Queue\ShouldQueue;
use Yansongda\LaravelPay\Facades\Pay;
use Yansongda\Pay\Exception\ContainerException;
use Yansongda\Pay\Exception\InvalidParamsException;
use Yansongda\Pay\Exception\ServiceNotFoundException;

class CheckAndChargeBalanceJob extends Job implements ShouldQueue
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
        (new Balance)->where('paid_at', null)->chunk(100, function ($balances) {
            foreach ($balances as $balance) {
                if (!$this->checkAndCharge($balance, true)) {
                    if (now()->diffInDays($balance->created_at) > 1) {
                        $balance->delete();
                    }
                }
            }
        });

        (new Balance)->where('paid_at', null)->where('created_at', '<', now()->subDays(2))->delete();
    }

    public function checkAndCharge(Balance $balance, $check = false): bool
    {

        if ($check) {
            try {
                $alipay = Pay::alipay()->find(['out_trade_no' => $balance->order_id]);
            } catch (ContainerException|InvalidParamsException|ServiceNotFoundException) {
                return false;
            }

            if ($alipay->trade_status !== 'TRADE_SUCCESS') {
                return false;
            }
        }

        if ($balance->paid_at !== null) {
            return true;
        }

        $balance->update([
            'paid_at' => now()
        ]);

        return true;
    }
}
