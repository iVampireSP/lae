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
    protected $description = '删除一个管理员';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        // 获取管理员ID
        $id = $this->ask('请输入管理员ID');

        // 删除管理员
        Admin::destroy($id);

        // 输出信息
        $this->info('管理员删除成功。');

        return CommandAlias::SUCCESS;
    }
}
