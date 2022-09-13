<?php

namespace App\Console\Commands;

use App\Models\Host;
use Illuminate\Console\Command;

class SuspendUserAllHosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:suspended {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '暂停用户的所有 Host';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //

        $user_id = $this->argument('user_id');

        Host::where('user_id', $user_id)->update([
            'status' => 'suspended',
            'suspended_at' => now()
        ]);

        $this->info('暂停用户的所有 Host 成功。');
    }
}
