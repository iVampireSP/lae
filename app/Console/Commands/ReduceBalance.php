<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;

class ReduceBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:reduce {user_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '减少用户的余额（发生退款时使用）';

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
     * @return void
     */
    public function handle(): void
    {
        //

        $user_id = $this->argument('user_id');

        $amount = $this->argument('amount');


        $user = (new User)->find($user_id);

        $this->warn('扣除金额: ' . $amount . ' 元');

        $this->warn('用户当前余额：' . $user->balance . ' 元');

        $this->warn('剩余余额：' . $user->balance - $amount . ' 元');

        $confirm = $this->confirm('确认扣除？');

        $transaction = new Transaction();
        if ($confirm) {
            $transaction->reduceAmount($user_id, $amount, '控制台扣除。');

            $this->info('扣除成功。');
        } else {
            $this->info('取消扣除。');
        }
    }
}
