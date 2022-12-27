<?php

namespace App\Console\Commands;

use App\Models\Balance;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试用的命令。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {

        // 使用 SQL 语句，获取 Balance 中的今日收益(sum amount)，然后 dd 为 sql
        $sql = Balance::query()
            ->selectRaw('sum(amount) as amount')
            ->where('created_at', '>=', today())
            ->toSql();


        dd($sql);


        return CommandAlias::SUCCESS;
    }
}
