<?php

namespace App\Console\Commands\System;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class Up extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'up';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '关闭维护模式。';

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

        Cache::delete('system_down');

        $this->info('维护模式已关闭。');

        // maintenance_mode(false);
    }
}
