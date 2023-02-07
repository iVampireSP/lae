<?php

namespace App\Console\Commands\Cluster;

use App\Support\ClusterSupport;
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
    protected $signature = 'works';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '启动此应用程序。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // 检测目录下是否有 rr
        if (!file_exists(base_path('rr'))) {
            $this->warn('未找到 rr 文件，将自动下载。');

            // 获取操作系统是 darwin 还是 linux
            $os = Str::contains(PHP_OS, 'Darwin') ? 'darwin' : 'linux';

            // 获取操作系统是 arm64 还是 amd64
            $arch = Str::contains(php_uname('m'), ['aarch64', 'arm64']) ? 'arm64' : 'amd64';

            // 下载 rr
            $this->info('正在下载 rr。');

            $version = config('settings.roadrunner.version');

            $download_link = "https://github.sakurapuare.com/roadrunner-server/roadrunner/releases/download/v$version/roadrunner-$version-$os-$arch.tar.gz";

            $save_name = 'rr_download.tar.gz';

            // 下载（wget）
            exec("wget $download_link -O $save_name");
            exec("tar -zxvf $save_name");

            // 删除下载的压缩包
            exec("rm $save_name");

            // 提取解压目录下的 rr 文件
            exec("mv roadrunner-$version-$os-$arch/rr rr");

            // 删除解压目录
            exec("rm -rf roadrunner-$version-$os-$arch");

            // 设置 rr 可执行权限
            exec('chmod +x rr');
        }

        // 关闭 Octane
        Artisan::call('octane:stop');

        if (!config('settings.node.ip')) {
            $this->error('请先配置节点 IP。');

            return CommandAlias::FAILURE;
        }

        // 刷新配置缓存
        $this->info('正在刷新配置缓存。');
        Artisan::call('config:cache');
        Artisan::call('route:cache');

        // redis 创建一个 hash
        $this->info('正在注册节点。');
        ClusterSupport::registerThisNode();

        $this->info('初始化完成。');

        $this->info('正在启动集群协调任务。');

        $pid = pcntl_fork();
        if ($pid === -1) {
            $this->error('无法创建子进程。');

            return CommandAlias::FAILURE;
        } else if ($pid === 0) {
            // 再打开一个，负责 octane
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->error('无法创建子进程。');

                return CommandAlias::FAILURE;
            } else if ($pid === 0) {
                // 子进程
                $this->info('正在启动 Web。');

                $command = 'php artisan octane:start --host=0.0.0.0 --rpc-port=6001 --port=8000';
                $this->pipeCommand($command);
            } else {
                $this->report();
            }
        } else {
            // 父进程
            $this->work();
        }

        return CommandAlias::SUCCESS;
    }

    private function pipeCommand($command): void
    {
        $descriptor_spec = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $pipes = [];
        $process = proc_open($command, $descriptor_spec, $pipes);

        if (is_resource($process)) {
            while ($s = fgets($pipes[1])) {
                echo $s;
            }
        }

        // 命令结束后，关闭管道
        fclose($pipes[0]);

        fclose($pipes[1]);

        fclose($pipes[2]);

        // 关闭进程

        proc_close($process);
    }

    private function report(): void
    {
        $this->info('正在报告此系统，请保持此命令一直运行。');

        Artisan::call('config:cache');

        $cpu = $this->getCpuUsage();

        while (1) {
            ClusterSupport::publish('system_usage', [
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

    private function work(): void
    {
        $this->info('正在监听任务。');

        ClusterSupport::publish('node.online');
        ClusterSupport::listen('*', function ($event, $message) {
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
            },
            'cluster.restart.web' => function () {
                $this->info('正在重启 Web。');

                exec('php artisan octane:reload');

                ClusterSupport::publish('cluster.restarted.web');

                $this->info('Web 重启完成。');
            },
            'cluster.restart.all' => function () {
                $this->info('正在重启整个莱云。');

                $this->pipeCommand('supervisorctl restart all');

                ClusterSupport::publish('cluster.restarted.all');

                $this->info('整个莱云 重启完成。');
            },
        ];

        if (isset($events[$event])) {
            $this->warn("正在处理 $event 事件。");
            $events[$event]($message);
        }
    }
}
