<?php

namespace App\Console\Commands\Admin;

use App\Models\Admin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置一个管理员的密码';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 获取管理员ID
        $id = $this->ask('请输入管理员 ID');

        // 获取管理员
        $admin = (new Admin)->find($id);

        // 验证管理员
        if (is_null($admin)) {
            $this->error('管理员不存在。');

            return CommandAlias::FAILURE;
        }

        // 密码
        $password = $this->secret('请输入密码');

        $admin->password = bcrypt($password);
        $admin->save();

        // 输出信息
        $this->info('管理员密码重置成功。');

        return CommandAlias::SUCCESS;
    }
}
