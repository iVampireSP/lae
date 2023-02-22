<?php

namespace App\Console\Commands\Admin;

use App\Models\Admin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Delete extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除一个管理员。';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 获取管理员ID
        $id = $this->ask('请输入管理员 ID');

        // 搜索
        $admin = Admin::find($id);
        if (is_null($admin)) {
            $this->error('管理员不存在。');

            return CommandAlias::FAILURE;
        }

        // 输出信息
        $this->table(['ID', '名称', '邮箱'], [
            [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
            ],
        ]);

        // 确认
        if (! $this->confirm('确认删除管理员吗？')) {
            return CommandAlias::FAILURE;
        }

        // 删除管理员
        Admin::destroy($id);

        // 输出信息
        $this->info('管理员删除成功。');

        return CommandAlias::SUCCESS;
    }
}
