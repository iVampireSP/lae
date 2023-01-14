<?php

namespace App\Console\Commands\Cluster;

use App\Support\ClusterSupport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as CommandAlias;
use ZipArchive;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:sync {--force=false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '下载配置。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('正在下载配置。');

        $node_type = config('settings.node.type');

        if ($node_type === 'master') {

            // if force is true, delete the old file
            if ($this->option('force') === 'true') {
                $confirm = 'yes';
            } else {
                $confirm = $this->ask('主节点同步将会恢复上一次数据，确定吗？', true);

            }


            if (!$confirm) {
                $this->warn('已取消。');
                return CommandAlias::SUCCESS;
            }
        }

        $cache_key = "master_config_zip";
        $config = ClusterSupport::get($cache_key);

        if ($config) {
            $this->info('检查下载目录的 MD5 值。');
            $config_md5_key = "master_config_zip_md5";
            $config_md5 = ClusterSupport::get($config_md5_key, '');

            $md5 = md5($config);
            if ($md5 !== $config_md5) {
                $this->error('下载目录 MD5 值被篡改。请尝试从其他节点重新同步。');
                return CommandAlias::FAILURE;
            }

            // 将缓存写入文件
            $this->info('正在写入文件。');

            $dir = 'cluster/config.zip';

            Storage::disk('local')->put($dir, $config);

            $path = Storage::disk('local')->path($dir);

            // 删除 config 目录
            $this->info('正在删除 config 目录。');
            $cache_path = base_path('config');

            // exec
            $cmd = "rm -rf $cache_path";
            exec($cmd);


            $this->info('正在解压缩。');
            $zip = new ZipArchive();
            $zip->open($path);
            $zip->extractTo(base_path());
            $zip->close();

            if ($node_type === 'slave') {
                // 下载 .env 文件
                $this->info('正在下载 .env 文件。');
                $env_cache_key = "{$node_type}_env";
                $env_md5_key = "{$node_type}_env_md5";

                $env = ClusterSupport::get($env_cache_key);
                $env_md5 = ClusterSupport::get($env_md5_key);

                $this->info('检查 .env 文件的 MD5 值。');
                if (md5($env) !== $env_md5) {
                    $this->error('.env 文件 MD5 值被篡改。请尝试从其他节点重新同步。');
                    return CommandAlias::FAILURE;
                } else {
                    $this->info('正在写入 .env 文件。');

                    // $node_ip
                    $node_ip = config('settings.node.ip');

                    // 覆盖 .env 文件
                    file_put_contents(base_path('.env'), $env);

                    // 还原 $node_ip 到 .env
                    $this->info('正在还原 .env 文件中的 NODE_IP。');
                    $env = file_get_contents(base_path('.env'));

                    // REPLACE NODE_IP 这一行
                    $env = preg_replace('/^NODE_IP=.*$/m', "NODE_IP=$node_ip", $env);

                    file_put_contents(base_path('.env'), $env);

                }

            }

            $this->info('正在清理。');

            // 删除目录
            Storage::disk('local')->delete($dir);

            // 刷新配置
            $this->info('正在刷新配置。');
            Artisan::call('optimize');

            // 上报消息
            ClusterSupport::publish('synced');
        } else {
            $this->error('没有找到缓存。请尝试从其他节点重新同步。');
            return CommandAlias::FAILURE;
        }


        return CommandAlias::SUCCESS;
    }
}
