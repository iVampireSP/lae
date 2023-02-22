<?php

namespace App\Jobs\WorkOrder;

use App\Jobs\Job;
use App\Models\WorkOrder\WorkOrder;

class AutoCloseWorkOrderJob extends Job
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // close work order after 1 day
        (new WorkOrder)->where('updated_at', '<=', now()->subDay())->update(['status' => 'closed']);
    }
}
