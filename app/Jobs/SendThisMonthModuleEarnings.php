<?php

namespace App\Jobs;

use App\Models\Module;
use App\Notifications\ModuleEarnings;
use Illuminate\Support\Facades\Cache;

class SendThisMonthModuleEarnings extends Job
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


        $default = [
            'balance' => 0,
            'drops' => 0,
        ];

        $rate = config('drops.module_rate');

        Module::chunk(
            100,
            function ($modules) use ($default, $rate) {
                foreach ($modules as $module) {
                    $data = [
                        'transactions' => [
                            'this_month' => Cache::get('this_month_balance_and_drops_' . $module->id, $default),
                            'last_month' => Cache::get('last_month_balance_and_drops_' . $module->id, $default),
                        ],
                        'rate' => $rate,
                    ];

                    (new ModuleEarnings($module))
                        ->toGroup($data);
                }
            }
        );
    }
}
