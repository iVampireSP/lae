<?php

namespace App\Console\Commands\Cluster;

use App\Support\ClusterSupport;
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
        ClusterSupport::listen('*', function ($event, $message) {
            $this->format($event, $message);
        }, false);

        return CommandAlias::SUCCESS;
    }

    private function format(string $event, array $message = [])
    {
        $status = $this->switch($event, $message['data']);

        if (!$status) {
            return;
        }

        $message = "[{$message['node']['type']}] {$message['node']['id']}:$event: " . $status;

        $this->info($message);
    }

    public function switch($event, $message = []): string|null
    {
        $events = [
            'node.ok' => '此节点初始化成功，并且已经加入集群。',
            'node.online' => '此节点已经上线。',
            'node.offline' => '将不再处理任何任务。',
            'cluster_ready.ok' => 'ClusterSupport Ready 就绪了，已经可以处理请求了。',
            'config.updated' => '集群配置文件已经更新，请所有 slave 节点下载。',
            'config.ssl.updated' => '边缘节点的证书已经更新。',
            'config.synced' => '我已下载配置文件。',
            'edge.deployed' => '已成功根据集群节点生成配置文件并应用。',
            'edge.launched' => '边缘节点成功启动。',
            'edge.error' => fn($message) => $message['message'] ?? '未知错误',
            'cluster.restart.web' => '正在重启 web 服务。',
            'cluster.restart.all' => '正在重启 整个 服务。',
            'cluster.restarted.web' => 'Web 重启好了。',
            'cluster.restarted.all' => '整个 重启好了。',
            'cluster.deployed' => '集群配置文件已经部署。',
            'cluster.deployed.error' => fn($message) => $message['message'] ?? '未知错误',
            'cluster.deployed.ok' => '集群配置文件部署成功。',
            'http.incoming' => fn($message) => $this->handleIncomingRequest($message),
            'http.outgoing' => fn($message) => $this->handleOutgoingRequest($message),
        ];

        $resp = $events[$event] ?? null;

        // if resp is callable
        if (is_callable($resp)) {
            return $resp($message);
        }

        return $resp ?? null;
    }

    private function handleIncomingRequest(array $message): string
    {
        $msg = $this->appendUser($message);

        $msg .= "{$message['method']} {$message['path']}";

        return $msg;
    }

    private function handleOutgoingRequest(array $message): string
    {
        $msg = $this->appendUser($message);

        $msg .= "{$message['method']} {$message['path']} {$message['status']} {$message['time']}ms";

        return $msg;
    }

    private function appendUser(array $message): string
    {
        $msg = '';
        if ($message['user']) {
            $msg .= "{$message['user']['name']}#{$message['user']['id']} ";
        } else {
            $msg .= 'Guest ';
        }

        return $msg;
    }
}
