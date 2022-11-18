<?php

namespace App\Console\Commands;

use App\Exceptions\ChargeException;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;

class UserAddBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:charge {user_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为用户充值，用法: 用户ID, 金额。';

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

        $user_id = $this->argument('user_id');
        $amount = $this->argument('amount');


        // find user
        $user = User::findOrFail($user_id);

        $this->info($user->name . ', 当前余额: ' . $user->balance . ' 元');

        $this->info('充值后余额: ' . ($user->balance + $amount) . ' 元');
        if (!$this->confirm('确认充值 ' . $amount . ' 元?')) {
            $this->info('已取消。');
            return 0;
        }

        $transaction = new Transaction();

        $description = '控制台充值 ' . $amount . ' 元';

        try {
            $transaction->addAmount($user->id, 'console', $amount, $description, true);

            $this->info('充值成功。');

            $user->refresh();
            $this->info($user->name . ', 当前余额: ' . $user->balance);
        } catch (ChargeException $e) {

            return $this->error($e->getMessage());
        }

        return 0;

    }
}
