<?php

namespace App\Console\Commands\Cluster;

use App\Support\Cluster;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Restart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:restart {service}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重启集群服务，支持的 service 有 web 和 all。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // 检测 service 参数
        $service = $this->argument('service');

        if (!in_array($service, ['web', 'all'])) {
            $this->error('service 参数错误，只能是 web 或 all。');
            return CommandAlias::FAILURE;
        }

        Cluster::publish('cluster.restart.' . $service);

        $this->info('已经向集群广播重启命令，等待集群响应。');

        return CommandAlias::SUCCESS;
    }
}
