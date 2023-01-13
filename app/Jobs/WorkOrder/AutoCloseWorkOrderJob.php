<?php

namespace App\Jobs\WorkOrder;

use App\Jobs\Job;
use App\Models\WorkOrder\WorkOrder;

class AutoCloseWorkOrderJob extends Job
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
        // close work order after 1 day
        (new WorkOrder)->where('updated_at', '<=', now()->subDay())->update(['status' => 'closed']);
    }
}
