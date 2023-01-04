<?php

namespace App\Console\Commands\Cluster;

use App\Support\Cluster;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Log extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '监听集群消息。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Cluster::listen('*', function ($event, $message) {
            $this->format($event, $message);
        }, false);

        return CommandAlias::SUCCESS;
    }

    private function format(string $event, array $message = [])
    {
        $status = $this->switch($event);

        if (!$status) {
            return;
        }

        $message = "[{$message['node']['type']}] {$message['node']['id']}:{$event}: " . $status;

        $this->info($message);
    }

    public function switch($event): string|null
    {
        $events = [
            'node.ok' => '此节点初始化成功，并且已经加入集群。',
            'node.online' => '此节点已经上线。',
            'node.offline' => '将不再处理任何任务。',
            'cluster_ready.ok' => 'Cluster Ready 就绪了，已经可以处理请求了。',
            'config.updated' => '集群配置文件已经更新，请所有 slave 节点下载。',
            'config.synced' => '我已下载配置文件。',
        ];

        return $events[$event] ?? null;
    }
}
