<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class UserAddBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:add-balance {user_id} {amount}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '为用户充值。';

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
     * @return int
     */
    public function handle(): int
    {
        $user_id = $this->argument('user_id');
        $amount = $this->argument('amount');

        // find user
        $user = (new User)->findOrFail($user_id);

        $this->info($user->name.', 当前余额: '.$user->balance.' 元');

        $this->info('充值后余额: '.($user->balance + $amount).' 元');
        if (! $this->confirm('确认充值 '.$amount.' 元?')) {
            $this->info('已取消。');

            return 0;
        }

        $description = '控制台充值 '.$amount.' 元';

        $user->charge($amount, 'console', $description);

        $this->info('充值成功。');

        $user->refresh();

        $this->info($user->name.', 当前余额: '.$user->balance);

        return CommandAlias::SUCCESS;
    }
}
