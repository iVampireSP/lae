<?php

namespace App\Console\Commands\Cluster;

use App\Support\ClusterSupport;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CleanOffline extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:clean-offline';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清理心跳超过 30 秒的节点。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $nodes = ClusterSupport::nodes();

        foreach ($nodes as $node) {
            $now = time();
            if ($now - $node['last_heartbeat'] > 30) {
                $this->info("节点 {$node['id']} 已离线，将被清理。");
                ClusterSupport::removeNode($node['id']);

                if ($node['type'] == 'edge') {
                    // 移除前 5 个字符，即 "edge-"。
                    $id = substr($node['id'], 5);
                    ClusterSupport::removeNode($id);
                }
            }
        }

        return CommandAlias::SUCCESS;
    }
}
