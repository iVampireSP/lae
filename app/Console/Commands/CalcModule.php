<?php

namespace App\Console\Commands;

use App\Http\Controllers\Remote\ModuleController;
use App\Models\Module\Module;
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
    protected $description = '计算模块的本月收益。';

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
        //
        $moduleController =  new ModuleController();

        $modules = Module::get();

        // 当前时间
        $now = now();

        $this->warn('开始计算集成模块收益。');
        $this->warn('当前时间: ' . $now);

        foreach ($modules as $module) {
            $report = $moduleController->calcModule($module);


            $income = $report['transactions']['this_month']['drops'] / $report['balance']['rate'];

            if ($income < 0) {
                $income = 0;
            }

            // 取 2 位
            $income = round($income, 2);

            $text = $module->name . " 收益 {$income} 元 ";
            $this->info($text);
        }
    }
}
