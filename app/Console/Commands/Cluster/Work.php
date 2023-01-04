<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Work extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:work';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '开始集群协调任务。';

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
