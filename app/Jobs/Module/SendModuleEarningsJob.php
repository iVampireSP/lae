<?php

namespace App\Jobs\Module;

use App\Jobs\Job;
use App\Models\Module;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendModuleEarningsJob extends Job
{
    /**
     * Execute the job.
     */
    public function handle(): void
    {
        (new Module)->whereHasBalance('0.01')->chunk(100, function ($modules) {
            foreach ($modules as $module) {
                $this->send($module);
            }
        });
    }

    private function send(Module $module): void
    {
        $data = $module->calculate();

        if (! $data) {
            return;
        }

        // make wecom_key visible
        $wecom_key = $module->wecom_key ?? config('settings.wecom.robot_hook.billing');

        $text = "# $module->name 收益";
        foreach ($data as $year => $months) {
            // 排序 months 从小到大
            ksort($months);

            $total = 0;
            $total_should = 0;

            foreach ($months as $month => $m) {
                // bc 保留两位小数
                $total = bcadd($total, $m['balance'], 2);
                $total_should = bcadd($total_should, $m['should_balance'], 2);

                $text .= <<<EOF

==========
{$year}年 {$month}月
实收: {$total}元
应得: $total_should 元

EOF;
            }
        }

        $resp = Http::post('https://qyapi.weixin.qq.com/cgi-bin/webhook/send?key='.$wecom_key, [
            'msgtype' => 'markdown',
            'markdown' => [
                'content' => $text,
            ],
        ]);

        if ($resp->failed()) {
            Log::error('发送模块盈利到企业微信时失败', [
                'module' => $module->id,
                'data' => $data,
                'resp' => $resp->json(),
            ]);
        }
    }
}
