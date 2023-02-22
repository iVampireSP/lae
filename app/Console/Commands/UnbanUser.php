<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UnbanUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unban {user_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '解除封禁一个用户。';

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
    public function handle(): void
    {
        //

        $user_id = $this->argument('user_id');

        $user = (new User)->find($user_id);

        $this->info('解除封禁: '.$user->name);

        $user->banned_at = null;
        $user->save();

        $this->info('用户已解除封禁。');
    }
}
