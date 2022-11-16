<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UserAddDrops extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add-drops {user_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '给用户添加 Drops';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $user_id = $this->argument('user_id');
        $amount = $this->argument('amount');

        $user = User::findOrFail($user_id);

        $transaction = new Transaction();

        $current_drops = $transaction->getDrops($user->id);

        $this->info($user->name . ', 当前 ' . $current_drops. ' Drops');

        $this->info($user->name . ', 当前余额: ' . $user->balance . ' 元');

        $this->info('添加后 ' . $current_drops + $amount . ' Drops');

        if (!$this->confirm('确认添加 ' . $amount . ' Drops?')) {
            $this->info('已取消。');
            return;
        }

        $transaction->increaseDrops($user->id, $amount, '管理员添加 Drops', 'console');

        $this->info('添加成功。');

        return CommandAlias::SUCCESS;
    }
}
