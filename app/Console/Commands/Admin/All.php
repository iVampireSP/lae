<?php

namespace App\Console\Commands\Admin;

use App\Models\Admin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class All extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '显示出所有 管理员。';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {

        $admins = Admin::all();

        $this->table(['ID', '邮箱'], $admins->map(function ($admin) {
            return [
                'id' => $admin->id,
                'email' => $admin->email,
            ];
        })->toArray());

        return CommandAlias::SUCCESS;
    }
}
