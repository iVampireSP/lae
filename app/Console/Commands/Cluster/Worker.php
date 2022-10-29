<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;

class Worker extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '开始节点协调工作';

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

        $redis = app('redis');

        $instance_id = config('app.instance_id');


        while (true) {

            // get cpu usage
            $cpu = round($this->getCpuUsage(), 2);

            $redis->publish('cluster_ready', json_encode([
                'instance_id' => $instance_id,
                'cpu' => $cpu,
            ]));

            sleep(1);
        }
    }

    public function getCpuUsage()
    {
        $load = sys_getloadavg();
        return $load[0];
    }
}
