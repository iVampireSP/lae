<?php

namespace App\Console\Commands\Cluster;

use App\Support\Cluster;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
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
        $this->warn('正在初始化集群协调任务。');
        Artisan::call('init');
        Artisan::call('optimize');

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

        $cpu = $this->getCpuUsage();
        $memory = $this->getMemoryUsage();

        while (1) {
            Cluster::publish('system_usage', [
                'cpu' => $cpu,
                'memory' => $memory,
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

    private function getMemoryUsage(): float
    {
        // 检查 free 命令是否存在
        if (exec('which free')) {
            $free = exec('free');
        } else {

            // fake free
            $free = <<<EOF
               total        used        free      shared  buff/cache   available
Mem:            1982         334        1121         126         527        1380
Swap:              0           0           0
EOF;
        }

        $free = trim($free);

        $free_arr = explode("\n", $free);

        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        return round($mem[2] / $mem[1] * 100, 2);
    }
}
