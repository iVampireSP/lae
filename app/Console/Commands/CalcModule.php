<?php

namespace App\Console\Commands;

use App\Models\Module;
use Illuminate\Console\Command;

class CalcModule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'modules:calc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '获取模块的最近收益。';

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
        $this->warn('开始计算集成模块收益。');
        $this->warn('当前时间: ' . now());


        Module::chunk(100, function ($modules) {
            foreach ($modules as $module) {
                $this->warn('模块: ' . $module->name);
                $years = $module->calculate();

                foreach ($years as $year => $months) {
                    // 排序 months 从小到大
                    ksort($months);

                    $total = 0;
                    $total_should = 0;

                    foreach ($months as $month => $m) {
                        $total += round($m['balance'], 2);
                        $total_should += round($m['should_balance'], 2);
                        $this->info("{$module->name} {$year}年 {$month}月 实收: {$total}元 应得: {$total_should} 元");
                    }
                }
            }
        });

        $this->warn('计算模块收益完成。');
        $this->warn('完成时间: ' . now());

        return 1;
    }
}
