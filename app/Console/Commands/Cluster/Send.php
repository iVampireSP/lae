<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Illuminate\Redis\RedisManager;

class Send extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '发送节点心跳';


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
        //

        $this->info('将上报节点信息到集群中。');

        // 非堵塞
        // $this->redis->subscribe(['cluster:sync'], function ($message, $channel) {
        //     $this->info('收到同步请求');
        // });

        // echo '开始循环发送心跳';
        while (true) {

            // get cpu usage
            $cpu = round($this->getCpuUsage(), 2);

            echo "CPU: {$cpu}%\n";
            $this->redis->publish('cluster_ready', json_encode([
                'instance_id' => $this->instance_id,
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
