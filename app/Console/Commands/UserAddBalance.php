<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Transaction;
use App\Models\User\Balance;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

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


        $balance = new Balance();

        $this->info($user->name . ', 当前余额: ' . $user->balance);

        if (!$this->confirm('确认充值 ' . $amount . ' 元?')) {
            $this->info('已取消。');
            return;
        }

        $data = [
            'user_id' => $user->id,
            'amount' => $amount,
            'payment' => 'console',
        ];

        $balance = $balance->create($data);

        $transaction = new Transaction();

        DB::beginTransaction();
        try {
            $balance->user->increment('balance', $amount);

            $description = '控制台充值 ' . $amount . ' 元';
            $transaction->addIncomeBalance($balance->user_id, 'console', $amount, $description);

            $balance->update([
                'paid_at' => now(),
            ]);

            DB::commit();

            $this->info('充值成功。');

            $user->refresh();
            $this->info($user->name . ', 当前余额: ' . $user->balance);

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('充值失败。' . $e->getMessage());

            return;
        }
    }
}
