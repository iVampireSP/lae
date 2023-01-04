<?php

namespace App\Console\Commands;

use App\Support\Cluster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '注册此节点到集群中。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Artisan::call('optimize');

        if (!config('settings.node.ip')) {
            $this->error('请先配置节点 IP。');
            return CommandAlias::FAILURE;
        }

        // 重写 .env 文件中的 NODE_ID
        $this->info('正在重写 .env 文件中的 NODE_ID。');

        $node_id = Str::random(8);

        if (config('settings.node.type') === 'master') {
            $node_id = 'master';
        }

        $env = file_get_contents(base_path('.env'));

        $env = preg_replace('/^NODE_ID=.*$/m', 'NODE_ID=' . $node_id, $env);

        file_put_contents(base_path('.env'), $env);


        // 刷新配置缓存
        $this->info('正在刷新配置缓存。');
        Artisan::call('config:cache');

        // redis 创建一个 hash
        $this->info('正在注册节点。');
        Cluster::registerThisNode();

        $this->info('初始化完成。');

        return CommandAlias::SUCCESS;
    }
}
