<?php

namespace App\Console\Commands;

use App\Models\Host;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WorkOrder\Reply;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Console\Command;
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

        $this->info('正在获取用户数量...');
        $users = User::count();
        $transactions = new Transaction();

        // 获取今年的交易记录 (MongoDB)
        $startOfYear = now()->startOfYear();
        $endOfYear = now()->endOfYear();

        $this->info('正在获取交易记录...');
        $transactions = Transaction::where('type', 'payout')->whereBetween('created_at', [$startOfYear, $endOfYear])->count();

        $this->info('正在获取主机数量...');
        $hosts = Host::count();

        $this->info('正在获取激活的主机数量...');
        $active_hosts = Host::active()->count();

        $this->info('正在获取暂停的主机数量...');
        $suspended_hosts = Host::whereNull('suspended_at')->count();

        $this->info('正在获取工单数量...');
        $workOrders = WorkOrder::count();

        $this->info('正在获取工单回复数量...');
        $replies = Reply::count();

        $this->info('统计服务器...');
        $servers = Cache::get('servers', []);
        $servers = count($servers);

        $this->info('完成。');


        $this->warn('用户数量: ' . $users);
        $this->warn('主机数量: ' . $hosts);
        $this->warn('正常的主机数量: ' . $active_hosts);
        $this->warn('暂停的主机数量: ' . $suspended_hosts);
        $this->warn('服务器数量: ' . $servers);
        $this->warn('工单数量: ' . $workOrders);
        $this->warn('工单回复数量: ' . $replies);
        $this->warn('今年的交易记录: ' . $transactions . ' 条');

        return 0;
    }
}
