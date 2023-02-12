<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Test extends Command
{
    // 测试用的

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        ini_set('memory_limit', '1024M');

        $storage = Storage::disk('s3');

        // $url = "https://ivampiresp.com";
        // $url = 'http://huge.test/send.php';
        $url = 'http://huge.test/128';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $start = 0;
        $end = 1024;
        // $append_size = 1024 * 1024;
        // $append_size  为 512kb
        $append_size = 1024 * 512;
        $total_size = 0;
        $downloaded_size = 0;

        $support = false;

        // 先发送一个 HEAD 请求，获取文件大小
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        $resp = curl_exec($ch);
        $total_size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        echo 'total size: '.$total_size.' bytes'.PHP_EOL;

        // 计算 append_size 多大合适
        // if ($total_size > 1024 * 1024 * 1024) {
        //     $append_size = 1024 * 1024 * 1024;
        // } elseif ($total_size > 1024 * 1024) {
        //     $append_size = 1024 * 1024;
        // }

        // 将 append_size 转换为 MB
        $append_size_mb = $append_size / 1024 / 1024;

        echo 'append size: '.$append_size_mb.' MB'.PHP_EOL;

        // 重置
        curl_setopt($ch, CURLOPT_NOBODY, 0);

        // 创建一个空文件
        $storage->put('download.bin', '');

        do {
            if (! $support) {
                // 设置下载范围
                curl_setopt($ch, CURLOPT_RANGE, "$start-$end");
                $data = curl_exec($ch);
                $current_size = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);

                // file_put_contents('download.bin', $data, FILE_APPEND);
                $storage->append('download.bin', $data, null);

                // $bukkit_name = config('filesystems.disks.cos.bucket') . '-' . config('filesystems.disks.cos.app_id');
                // cos via app
                // dd(app('cos'));
                // $result = app('cos')->appendObject([
                //     'Bucket' => $bukkit_name,
                //     'Key' => 'download.bin',
                //     'Position' => $start, //追加对象位置
                //     'Body' => $data,      //读取文件内容
                // ]);

                $downloaded_size += $current_size;
                $start = $end + 1;
                $end += $append_size;

                // 判断是否下载完毕
                if ($downloaded_size >= $total_size) {
                    break;
                }

                // download progress
                echo 'downloaded: '.$downloaded_size.' of '.$total_size.' bytes'.PHP_EOL;
            } else {
                echo 'server not support range download'.PHP_EOL;
                // exit;

                // stream download
                $fp = fopen('download.bin', 'w');
                curl_setopt($ch, CURLOPT_FILE, $fp);
                $data = curl_exec($ch);

                fclose($fp);

                echo 'download complete without range';

                break;
            }
        } while ($current_size < $total_size);

        curl_close($ch);

        return CommandAlias::SUCCESS;
    }
}
