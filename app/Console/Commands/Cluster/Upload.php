<?php

namespace App\Console\Commands\Cluster;

use App\Support\ClusterSupport;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command as CommandAlias;
use ZipArchive;

class Upload extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:upload';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将此节点配置文件上传到集群中。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $node_type = config('settings.node.type');

        if ($node_type === 'slave') {
            $this->info('正在同步从节点配置文件。');

            $this->upload('slave');

            $this->info('从节点配置文件同步完成。');

            return CommandAlias::SUCCESS;
        }

        $this->warn('此节点为主节点，将同时上传两份版本（如果有 .env.slave 的话）。以及让边缘节点重载 SSL。');

        // 上传 master
        $this->upload('master');

        // 检测 .env.slave 是否存在
        if (file_exists(base_path('.env.slave'))) {
            // 备份当前的 .env 文件为 .env.temp
            $this->info('正在备份 .env 文件。');
            copy(base_path('.env'), base_path('.env.temp'));

            // 复制 .env.slave 为 .env
            $this->info('正在复制 .env.slave 文件。');
            copy(base_path('.env.slave'), base_path('.env'));

            $this->upload('slave');

            // 恢复 .env 文件
            $this->info('正在恢复 .env 文件。');
            copy(base_path('.env.temp'), base_path('.env'));

            // 删除 .env.temp
            unlink(base_path('.env.temp'));
        }

        ClusterSupport::publish('config.updated');

        $this->info('节点初始化完成。');

        if (app()->environment() === 'local') {
            $this->info('清理开发节点。');

            Artisan::call('route:clear');
            Artisan::call('config:clear');
        }

        return CommandAlias::SUCCESS;
    }

    public function upload($node_type)
    {
        $this->warn("正在上传 $node_type  文件。");
        $this->info('正在打包 config 目录。');

        if ($node_type === 'master') {
            // 相对路径
            $cache = 'config';

            $cacheZip = $cache . '.zip';
            $zip = new ZipArchive();
            $zip->open($cacheZip, ZipArchive::CREATE);
            $this->addFileToZip($cache, $zip);
            $zip->close();

            $this->info('正在上传 config 目录。');

            $cache_key = "{$node_type}_config_zip";
            ClusterSupport::forever($cache_key, file_get_contents($cacheZip));

            // md5
            $this->info('正在报告 cache 目录的 MD5 值。');
            $cache_md5_key = "{$node_type}_config_zip_md5";
            ClusterSupport::forever($cache_md5_key, md5_file($cacheZip));

            unlink($cacheZip);

            // 上传 config/secrets/ssl_fullchain.pem 和 config/secrets/ssl_privkey.pem
            $ssl_fullchain_key = 'config/secrets/ssl_fullchain.pem';
            $ssl_privkey_key = 'config/secrets/ssl_privkey.pem';

            if (file_exists(base_path($ssl_fullchain_key)) && file_exists(base_path($ssl_privkey_key))) {
                $this->info('正在上传 SSL 证书。');

                ClusterSupport::forever('ssl_fullchain', file_get_contents(base_path($ssl_fullchain_key)));
                ClusterSupport::forever('ssl_privkey', file_get_contents(base_path($ssl_privkey_key)));

                // 计算 md5
                $ssl_fullchain_md5 = md5_file(base_path($ssl_fullchain_key));
                $ssl_privkey_md5 = md5_file(base_path($ssl_privkey_key));

                $this->info('正在报告 SSL 证书的 MD5 值。');
                ClusterSupport::forever('ssl_fullchain_md5', $ssl_fullchain_md5);
                ClusterSupport::forever('ssl_privkey_md5', $ssl_privkey_md5);

                ClusterSupport::publish('config.ssl.updated');
            } else {
                $this->warn('SSL 证书不存在，跳过。');
            }
        }

        // 上传 .env 文件
        $this->info('正在上传 .env 文件。');
        $env_key = "{$node_type}_env";
        ClusterSupport::forever($env_key, file_get_contents(base_path('.env')));

        // 上传 .env 文件的 MD5
        $this->info('正在报告 .env 文件的 MD5 值。');
        $env_md5_key = "{$node_type}_env_md5";
        ClusterSupport::forever($env_md5_key, md5_file(base_path('.env')));

        $this->info('完成。');
    }

    public function addFileToZip(string $path, ZipArchive $zip): void
    {
        $handler = opendir($path);
        while (($filename = readdir($handler)) !== false) {
            if ($filename != '.' && $filename != '..') {
                if (is_dir($path . '/' . $filename)) {
                    $this->addFileToZip($path . '/' . $filename, $zip);
                } else {
                    $zip->addFile($path . '/' . $filename);
                }
            }
        }
        @closedir($handler);
    }
}
