<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Down extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'down';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '启动维护模式。';

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
     * @return mixed
     */
    public function handle()
    {
        //

        // 记录到缓存(维护最长 2 小时)

        Cache::put('system_down', true, now()->addHours(2));

        $this->info('API 已进入维护模式，将在 2 小时后自动关闭。');
        $this->warn('请注意，维护模式只会拦截用户的请求，不会影响模块通信。');

    }
}
