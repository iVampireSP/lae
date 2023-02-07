<?php

namespace App\Console\Commands;

use App\Models\Host;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class HostCost extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'host:cost {--host-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '手动扣费';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $host = $this->option('host-id');

        if (empty($host)) {
            if ($this->confirm('如果不指定主机名，将会扣除所有主机的费用，是否继续？', true)) {
                (new Host)->chunk(100, function ($hosts) {
                    foreach ($hosts as $host) {
                        $this->info('正在扣除主机 ' . $host->name . ' 的费用: ' . $host->getPrice() . ' 元');
                        $host->cost();
                    }
                });
            }
        } else {
            $host_model = (new Host)->where('id', $host)->firstOrFail();

            if ($this->confirm('是否扣除主机 ' . $host_model->name . ' 的费用？', true)) {
                $host_model->cost();
            }
        }

        return CommandAlias::SUCCESS;
    }
}
