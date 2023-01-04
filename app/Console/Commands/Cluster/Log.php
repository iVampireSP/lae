<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Log extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:log';

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
        return CommandAlias::SUCCESS;
    }
}
