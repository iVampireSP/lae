<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class Status extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取 莱云 运行环境';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->warn('===== 运行环境 =====');

        // php version
        $this->info('PHP 版本: ' . PHP_VERSION);

        // get mysql version
        /** @noinspection UnknownColumnInspection */
        $mysql_version = DB::select('select version() as version')[0]->version;

        $this->warn('MySQL 版本: ' . $mysql_version);

        $redis_info = Redis::info();

        // get redis version
        $redis_version = $redis_info['redis_version'];
        $this->warn('Redis 版本: ' . $redis_version);

        // 是否是 Redis 集群
        $redis_cluster = $redis_info['cluster_enabled'];

        if ($redis_cluster == 1) {
            $this->warn('Redis 集群: 是');
        } else {
            $this->warn('Redis 集群: 否');
        }

        // redis 操作系统
        $redis_os = $redis_info['os'];
        $this->warn('Redis 操作系统: ' . $redis_os);

        // redis process id
        $redis_pid = $redis_info['process_id'];
        $this->warn('Redis 进程 ID: ' . $redis_pid);

        // redis used memory
        $redis_used_memory = $redis_info['used_memory_human'];

        $this->info('Redis 内存: ' . $redis_used_memory);

        // redis memory peak
        $redis_memory_peak = $redis_info['used_memory_peak_human'];
        $this->info('Redis 内存峰值: ' . $redis_memory_peak);

        // redis memory lua
        $redis_memory_lua = $redis_info['used_memory_lua_human'];
        $this->info('Redis Lua 内存: ' . $redis_memory_lua);

        // redis 连接
        $redis_connected_clients = $redis_info['connected_clients'];
        $this->info('Redis 连接数量: ' . $redis_connected_clients);

        // redis 命中率
        $redis_keyspace_hits = $redis_info['keyspace_hits'];
        $redis_keyspace_misses = $redis_info['keyspace_misses'];
        $redis_keyspace_hit_rate = round($redis_keyspace_hits / ($redis_keyspace_hits + $redis_keyspace_misses) * 100, 2);
        $this->info('Redis 命中率: ' . $redis_keyspace_hit_rate . '%');

        // redis 总连接数
        $redis_total_connections_received = $redis_info['total_connections_received'];
        $this->info('Redis 总连接数: ' . $redis_total_connections_received);

        // redis total commands
        $redis_total_commands_processed = $redis_info['total_commands_processed'];
        $this->info('Redis 总命令数: ' . $redis_total_commands_processed);

        $this->warn('===== 莱云 统计 =====');
        $this->warn('要获取 莱云 统计信息，请运行 count 命令。');

        return 0;
    }
}
