<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class BanUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ban {user_id} {reason}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '封禁一个用户。';

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
     */
    public function handle(): int
    {
        //

        $user_id = $this->argument('user_id');

        $reason = $this->argument('reason');

        $user = (new User)->find($user_id);

        $this->info('封禁: '.$user->name);

        $this->confirm('确定要继续吗？如果继续，将会暂停所有的主机，并且吊销所有 Token。');

        $user->banned_at = now();
        $user->banned_reason = $reason;
        $user->save();

        $this->info('封禁用户成功。');

        return 0;
    }
}
