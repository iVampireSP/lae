<?php

namespace App\Console\Commands\Cluster;

use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class ReportSystem extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cluster:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '报告此系统';

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
