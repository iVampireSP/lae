<?php

namespace App\Jobs;

use App\Models\WorkOrder\WorkOrder;

class AutoCloseWorkOrder extends Job
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
        // closed replied after 1 day
        WorkOrder::where('status', 'replied')->where('updated_at', '<=', now()->subDay())->update(['status' => 'closed']);
    }
}
