<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Illuminate\Redis\RedisManager;

class Work extends Command
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


    protected RedisManager $redis;
    protected String $instance_id;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->redis = app('redis');
        $this->instance_id = config('app.instance_id');
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('节点协调工作开始');

        $this->redis->subscribe(['cluster_ready'], function ($message, $channel) {

            $message = json_decode($message, true);

            var_dump($message);
        });
    }


    public function subscribe()
    {

        // 非堵塞模式
        $this->redis->subscribe(['cluster_ready'], function ($message, $channel) {
            echo "Received {$message} from {$channel}\n";
        });
    }


    public function publish()
    {
        $this->redis->publish('cluster_ready', json_encode([
            'instance_id' => $this->instance_id,
            'cpu' => 1
        ]));
    }
}
