<?php

namespace App\Console\Commands\Cluster;

use App\Support\ClusterSupport;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Monitor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor {--ignore_event=}';

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
        $ignore_events = [];

        if ($this->option('ignore_event')) {
            $ignore_events = explode(',', $this->option('ignore_event'));
        }

        ClusterSupport::publish('monitor.started');

        ClusterSupport::listen('*', function ($event, $message) use ($ignore_events) {
            if (in_array($event, $ignore_events)) {
                return;
            }

            $this->format($event, $message);
        }, false);

        return CommandAlias::SUCCESS;
    }

    public function format(string $event, array $message = [], $stdout = true): ?string
    {
        $status = $this->switch($event, $message['data']);

        if (! $status) {
            return null;
        }

        $now = now()->toDateTimeString();
        $message = "$now [{$message['node']['id']}] \t <$event>  \t".$status."\t";

        if ($stdout) {
            $this->info($message);

            return null;
        } else {
            return $message;
        }
    }

    public function switch($event, $message = []): string|null
    {
        $events = [
            'monitor.started' => '我正在监听集群消息。',
            'node.ok' => '此节点初始化成功，并且已经加入集群。',
            'node.online' => fn ($message) => '此节点已经上线，启动权重为：'.$message['weight'],
            'node.offline' => '将不再处理任何任务。',
            'cluster_ready.ok' => 'ClusterSupport Ready 就绪了，已经可以处理请求了。',
            'config.updated' => '集群配置文件已经更新，请所有 slave 节点下载。',
            'config.ssl.updated' => '边缘节点的证书已经更新。',
            'config.synced' => '我已下载配置文件。',
            'edge.deployed' => '已成功根据集群节点生成配置文件并应用。',
            'edge.launched' => '边缘节点成功启动。',
            'edge.error' => fn ($message) => $message['message'] ?? '未知错误',
            'cluster.restart.web' => '正在重启 web 服务。',
            'cluster.restart.all' => '正在重启 整个 服务。',
            'cluster.restarted.web' => 'Web 重启好了。',
            'cluster.restarted.all' => '整个 重启好了。',
            'cluster.deployed' => '集群配置文件已经部署。',
            'cluster.deployed.error' => fn ($message) => $message['message'] ?? '未知错误',
            'cluster.deployed.ok' => '集群配置文件部署成功。',
            'http.incoming' => fn ($message) => $this->handleIncomingRequest($message),
            'http.outgoing' => fn ($message) => $this->handleOutgoingRequest($message),
            'weight.updated' => fn ($message) => $this->handleWeightUpdated($message),
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

        $msg .= "{$message['method']} \t {$message['path']}";

        return $msg;
    }

    private function appendUser(array $message): string
    {
        $msg = '';
        if ($message['user']) {
            $msg .= "{$message['user']['name']}#{$message['user']['id']} \t";
        } else {
            $msg .= "Guest \t";
        }

        return $msg;
    }

    private function handleOutgoingRequest(array $message): string
    {
        $msg = $this->appendUser($message);

        $msg .= "{$message['method']} \t {$message['path']} \t {$message['status']} {$message['time']}ms";

        return $msg;
    }

    private function handleWeightUpdated(array $message): string
    {
        $msg = '';
        if ($message['weight'] === '0') {
            $msg .= '我不参与调度了。';
        } else {
            $msg .= '我的权重是：'.$message['weight'].'。';
        }

        return $msg;
    }
}
