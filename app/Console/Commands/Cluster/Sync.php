<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
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
    protected $signature = 'cluster:sync';

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

        $node_type = config('settings.node_type');

        $cache_key = "cluster:${node_type}_config";
        $config = Cache::get($cache_key);

        if ($config) {
            $this->info('检查下载目录的 MD5 值。');
            $config_md5_key = "cluster:${node_type}_config_md5";
            $config_md5 = Cache::get($config_md5_key, '');

            $md5 = md5($config);
            if ($md5 !== $config_md5) {
                $this->error('下载目录 MD5 值被篡改。请尝试从其他节点重新同步。');
                return CommandAlias::FAILURE;
            }

            // 将缓存写入文件
            $this->info('正在写入文件。');
            Storage::disk('local')->put('cluster/config.zip', $config);

            $path = Storage::disk('local')->path('cluster/config.zip');

            // 删除 config 目录
            $this->info('正在删除 config 目录。');
            $cache_path = base_path('config');

            // exec
            $cmd = "rm -rf ${cache_path}";
            exec($cmd);

            $this->info('正在解压缩。');

            $zip = new ZipArchive();
            $zip->open($path);
            $zip->extractTo(base_path());
            $zip->close();

            $this->info('正在清理。');

            // 删除目录
            Storage::disk('local')->deleteDirectory('cluster');
        } else {
            $this->error('没有找到缓存。请尝试从其他节点重新同步。');
            return CommandAlias::FAILURE;
        }


        return CommandAlias::SUCCESS;
    }
}
