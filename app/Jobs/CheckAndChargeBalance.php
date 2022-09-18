<?php

namespace App\Jobs;

use App\Http\Controllers\User\BalanceController;
use App\Models\User\Balance;

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


        // 查找今天未支付的订单
        Balance::where('paid_at', null)->chunk(100, function ($balances) use ($bc) {
            foreach ($balances as $balance) {
                if (!$bc->checkAndCharge($balance)) {
                    if (now()->diffInDays($balance->created_at) > 1) {
                        $balance->delete();
                    }
                }
            }
        });

        // Balance::chunk(100, function ($balances) use ($bc) {
        //     foreach ($balances as $balance) {

        //         $bc->checkAndCharge($balance);
        //     }
        // });
    }
}
