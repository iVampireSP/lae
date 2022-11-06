<?php

namespace App\Jobs;

use App\Http\Controllers\Api\BalanceController;
use App\Models\Balance;

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
    public function handle()
    {
        //

        $bc = new BalanceController();

        Balance::where('paid_at', null)->chunk(100, function ($balances) use ($bc) {
            foreach ($balances as $balance) {
                if (!$bc->checkAndCharge($balance)) {
                    if (now()->diffInDays($balance->created_at) > 1) {
                        $balance->delete();
                    }
                }
            }
        });

        // 删除所有未付款并且大于两天的订单
        Balance::where('paid_at', null)->where('created_at', '<', now()->subDays(2))->delete();

        // Balance::chunk(100, function ($balances) use ($bc) {
        //     foreach ($balances as $balance) {

        //         $bc->checkAndCharge($balance);
        //     }
        // });
    }
}
