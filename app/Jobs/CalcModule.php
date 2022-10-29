<?php

namespace App\Jobs;

use App\Models\Transaction;
use App\Models\Module\Module;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Remote\ModuleController;

class CalcModule extends Job
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
        // begin of this month
        $beginOfMonth = now()->startOfMonth();

        // end of this month
        $endOfMonth = now()->endOfMonth();

        Module::chunk(100, function ($modules) use ($beginOfMonth, $endOfMonth) {
            foreach ($modules as $module) {

                $this_month = Transaction::where('module_id', $module->id)->where('type', 'payout')->whereBetween('created_at', [$beginOfMonth, $endOfMonth]);

                // this month transactions
                $this_month =  [
                    'balance' => $this_month->sum('outcome'),
                    'drops' => $this_month->sum('outcome_drops')
                ];

                Cache::put('this_month_balance_and_drops_' . $module->id, $this_month, 60 * 24 * 30);

                // last month transactions
                $last_moth = Transaction::where('module_id', $module->id)->where('type', 'payout')->whereBetween('created_at', [$beginOfMonth, $endOfMonth]);

                $last_moth =  [
                    'balance' => $last_moth->sum('outcome'),
                    'drops' => $last_moth->sum('outcome_drops')
                ];

                Cache::put('last_month_balance_and_drops_' . $module->id, $last_moth, 60 * 24 * 30);
            }
        });

        return 0;
    }
}
