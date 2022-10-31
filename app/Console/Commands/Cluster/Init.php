<?php

namespace App\Console\Commands\Cluster;

use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '初始化此节点。当公网 IP 发生变化时，需要重新初始化。';

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

        $this->warn('检查服务可用性');

        // 检查是否可以连接到数据库
        try {
            DB::connection()->getPdo();
            $this->info('数据库连接正常');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        // 检测是否可以连接到 Redis
        try {
            $redis = app('redis');
            $redis->ping();
            $this->info('Redis 连接正常');
        } catch (\Exception $e) {
            $this->error($e->getMessage());
        }

        if (config('app.instance_address')) {
            $addr = $this->info('当前实例地址: ' . config('app.instance_address'));
        } else {
            $this->info('正在获取公网 IP');
            // get public ip
            $addr = file_get_contents('https://ifconfig.me/ip');
            $this->warn('公网 IP: ' . $addr);
        }
        $port = config('laravels.listen_port');
        $type = config('app.instance_type');


        $this->info('正在准备节点');
        $instance_id = config('app.instance_id');

        $inst_data = [
            'instance_id' => $instance_id,
            'ip' => $addr,
            'port' => $port,
            'type' => $type,
        ];

        dd(cluster_run_wait('register', $inst_data));

        // 检测其他 莱云 计算节点
        // $nodes = Cache::get('nodes', collect([]));

        // 检测节点是否在集合里
        // $node = $nodes->where('instance_id', $instance_id)->first();

        // if ($node == null) {
        //     $this->warn('节点未注册');
        //     $this->info('正在注册节点');

        //     // add to collect
        //     $nodes->push([
        //         'instance_id' => $instance_id,
        //         'ip' => $addr,
        //         'port' => $port,
        //         'type' => $type,
        //     ]);

        //     $this->warn('节点注册成功');
        // } else {
        //     $this->warn('节点已注册');

        //     // 如果 IP 不同，则更新 IP
        //     if ($node['ip'] != $addr) {
        //         $this->info('正在更新节点 IP');
        //         $node['ip'] = $addr;
        //         $this->info('节点 IP 更新成功');
        //     }

        //     $node['port'] = $port;
        // }

        // // save cache
        // Cache::forever('nodes', $nodes);



        // 检测模块是否正常
        // Module::chunk(100, function ($modules) {
        //     foreach ($modules as $module) {
        //         $this->info('检测模块 ' . $module->name);
        //         if($module->check()) {
        //             $this->warn('模块 ' . $module->name . ' 正常');
        //         } else {
        //             $this->error('模块 ' . $module->name . ' 异常');
        //         }
        //     }
        // });
    }
}
