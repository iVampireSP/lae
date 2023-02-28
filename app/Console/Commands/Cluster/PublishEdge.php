<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Command\Command as CommandAlias;

class PublishEdge extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'edge:publish {--node-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '生成 edge.php 到 bin 目录下。';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // node_id 默认为随机字符串
        $node_id = $this->option('node-id') ?? Str::random(8);

        // 读取 stubs/edge.stub
        $stub = file_get_contents(base_path('stubs/edge'));

        // 替换 stubs/edge.stub 中的内容
        $redis_host = config('database.redis.default.host');
        $redis_port = config('database.redis.default.port');
        $redis_password = config('database.redis.default.password');
        $redis_prefix = config('database.redis.options.prefix');

        $stub = str_replace(
            [
                '{{REDIS_HOST}}',
                '{{REDIS_PORT}}',
                '{{REDIS_PASSWORD}}',
                '{{REDIS_PREFIX}}',
                '{{NODE_ID}}',
            ],
            [
                $redis_host,
                $redis_port,
                $redis_password,
                $redis_prefix,
                $node_id,
            ],
            $stub
        );

        // 写入 bin/edge.php
        file_put_contents(base_path('bin/edge.php'), $stub);

        $this->info('生成 edge.php 成功。请稍后编辑 bin/edge.php 文件。');
        $this->warn('此文件至关重要，请勿泄漏。');

        return CommandAlias::SUCCESS;
    }
}
