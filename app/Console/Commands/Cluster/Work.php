<?php

namespace App\Console\Commands\Cluster;

use App\Support\Cluster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

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
    protected $description = '开始集群协调任务。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Artisan::call('config:cache');

        if (!config('settings.node.ip')) {
            $this->error('请先配置节点 IP。');
            return CommandAlias::FAILURE;
        }

        // if not node_id
        if (!config('settings.node.id')) {
            // 重写 .env 文件中的 NODE_ID
            $this->info('正在重写 .env 文件中的 NODE_ID。');

            $node_id = Str::random(8);

            if (config('settings.node.type') === 'master') {
                $node_id = 'master';
            }

            $env = file_get_contents(base_path('.env'));

            $env = preg_replace('/^NODE_ID=.*$/m', 'NODE_ID=' . $node_id, $env);

            file_put_contents(base_path('.env'), $env);
        }


        // 刷新配置缓存
        $this->info('正在刷新配置缓存。');
        Artisan::call('config:cache');

        // redis 创建一个 hash
        $this->info('正在注册节点。');
        Cluster::registerThisNode();

        $this->info('初始化完成。');

        $this->info('正在启动集群协调任务。');
        $pid = pcntl_fork();
        if ($pid === -1) {
            $this->error('无法创建子进程。');
            return CommandAlias::FAILURE;
        } else if ($pid === 0) {
            // 子进程
            $this->report();
        } else {
            // 父进程
            $this->work();
        }


        return CommandAlias::SUCCESS;
    }


    private function work(): void
    {
        $this->info('正在监听任务。');

        Cluster::publish('node.online');
        Cluster::listen('*', function ($event, $message) {
            $this->dispatchEvent($event, $message);
        }, false);
    }

    private function dispatchEvent($event, $message = []): void
    {
        $events = [
            'config.updated' => function () {
                $this->info('正在更新配置文件。');

                Artisan::call('cluster:sync', [
                    '--force' => 'true',
                ]);

                $this->info('配置文件更新完成。');

            }
        ];

        if (isset($events[$event])) {
            $this->warn("正在处理 {$event} 事件。");
            $events[$event]($message);
        }
    }

    private function report(): void
    {
        $this->info('正在报告此系统，请保持此命令一直运行。');

        Artisan::call('config:cache');

        $cpu = $this->getCpuUsage();

        while (1) {
            Cluster::publish('system_usage', [
                'cpu' => $cpu,
            ]);

            sleep(1);
        }
    }

    private function getCpuUsage(): float
    {
        // 获取 CPU 使用率
        $cpu = sys_getloadavg();
        return $cpu[0];
    }
}
