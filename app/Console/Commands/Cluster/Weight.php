<?php

namespace App\Console\Commands\Cluster;

use App\Support\ClusterSupport;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Weight extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'weight {weight=1}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '设置当前节点的权重。设置为 0 时，当前节点不参与负载均衡。';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $weight = $this->argument('weight');

        $this->info("当前节点的权重为：{$weight}。");
        if ($weight == '0') {
            $this->warn('请求将不再调度到此节点。');
        } else {
            $this->info('将开始接受请求。');
        }

        ClusterSupport::updateThisNode([
            'weight' => $weight,
        ]);

        ClusterSupport::publish('weight.updated', [
            'weight' => $weight,
        ]);

        return CommandAlias::SUCCESS;
    }
}
