<?php

namespace App\Console\Commands\Admin;

use App\Models\Admin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个管理员账号。';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 名称
        $name = $this->ask('请输入名称');

        // 邮箱
        $email = $this->ask('请输入邮箱');

        // 密码
        $password = $this->secret('请输入密码');
        // 确认密码
        $password_confirmation = $this->secret('请再次输入密码');

        // 验证密码
        if ($password !== $password_confirmation) {
            $this->error('两次输入的密码不一致。');

            return CommandAlias::FAILURE;
        }

        // 创建管理员
        $admin = (new Admin)->create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
        ]);

        // 输出信息
        $this->info('管理员创建成功，ID 为: '.$admin->id.'。');

        return CommandAlias::SUCCESS;
    }
}
