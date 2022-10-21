<?php

namespace App\Console\Commands;

use App\Models\Host;
use App\Models\User;
use App\Models\Transaction;
use App\Models\WorkOrder\Reply;
use Illuminate\Console\Command;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Support\Facades\Cache;

class Count extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计 主机，用户，工单，一年的交易记录，服务器数量。';

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

        $users = User::count();
        $transactions = new Transaction();

        // 获取今年的交易记录
        $transactions = $transactions->whereYear('created_at', date('Y'));

        $transactions = $transactions->count();

        $hosts = Host::count();
        $workOrders = WorkOrder::count();
        $replies = Reply::count();

        $servers = Cache::get('servers', []);
        $servers = count($servers);

        $this->warn('用户数量: ' . $users);
        $this->warn('主机数量: ' . $hosts);
        $this->warn('服务器数量: ' . $servers);
        $this->warn('工单数量: ' . $workOrders);
        $this->warn('工单回复数量: ' . $replies);
        $this->warn('今年的交易记录: ' . $transactions);
    }
}
