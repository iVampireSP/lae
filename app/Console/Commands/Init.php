<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Init extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '不清理缓存情况下的 optimize';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->info('正在删除 bootstrap/cache 目录。');
        $cache_path = base_path('bootstrap/cache');
        $files = glob($cache_path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        Artisan::call('route:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');

        Artisan::call('optimize');

        return CommandAlias::SUCCESS;
    }
}
