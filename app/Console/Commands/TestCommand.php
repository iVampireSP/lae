<?php

namespace App\Console\Commands;

use App\Models\Balance;
use App\Models\User;
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

        // 使用 SQL 语句，统计 Balance 模型中，根据 payment 类别，计算出数据总和 （group by date and payment）(date 类型要求 datetime)
        $balances = Balance::selectRaw('date(created_at) as date, payment, sum(amount) as total')
            ->groupBy('date', 'payment')
            ->get();



        // table
        $this->table(['支付方式', '数量', '日期'], $balances->toArray());

        return CommandAlias::SUCCESS;
    }
}
