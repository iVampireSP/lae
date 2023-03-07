<?php

namespace App\Models;

use App\Exceptions\User\BalanceNotEnoughException;
use App\Jobs\Host\HostJob;
use App\Jobs\Host\UpdateOrDeleteHostJob;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Support\Facades\Cache;

class Host extends Model
{
    use Cachable;

    protected $table = 'hosts';

    protected $fillable = [
        'name',
        'module_id',
        'user_id',
        'price',
        'managed_price',
        'configuration',
        'status',
        'suspended_at',
        'trial_ends_at',
        'billing_cycle',
        'cancel_at_period_end',
        'last_paid',
        'last_paid_at',
        'expired_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'last_paid' => 'decimal:2',
        'managed_price' => 'decimal:2',
        'configuration' => 'array',
        'suspended_at' => 'datetime',
        'last_paid_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    /** @noinspection PhpUndefinedMethodInspection */
    public function getUserHosts($user_id = null): array|Collection
    {
        return $this->where('user_id', $user_id)->with('module', function ($query) {
            $query->select(['id', 'name']);
        })->get();
    }

    public function module(): BelongsToAlias
    {
        return $this->belongsTo(Module::class);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['running', 'stopped']);
    }

    // public function workOrders(): HasManyAlias
    // {
    //     return $this->hasMany(WorkOrder::class);
    // }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeExpiring($query)
    {
        return $query->where('status', 'running')->where('next_due_at', '<=', now()->addDays(7));
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeThisUser($query, $module = null)
    {
        if ($module) {
            return $query->where('user_id', auth()->id())->where('module_id', $module);
        } else {
            return $query->where('user_id', auth()->id());
        }
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    public function isUnavailable(): bool
    {
        return $this->status === 'unavailable';
    }

    public function getPrice(): float
    {
        return $this->managed_price ?? $this->price;
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function safeDelete(): bool
    {
        if ($this->isHourly()) {
            // 如果创建时间大于 1 小时
            if ($this->created_at->diffInHours(now()) > 1) {
                // 如果当前时间比扣费时间小，则说明没有扣费。执行扣费。
                if (now()->minute < $this->minute_at) {
                    $this->cost();
                }
            }
        } elseif ($this->isMonthly() && $this->last_paid && ! $this->isExpired()) {
            // 根据扣费时间，计算出退款金额
            $refund = $this->getRefundAmount();

            if ($refund) {
                // 如果有退款金额，则退款
                $this->module?->reduce($refund, 'module_balance', '主机 '.$this->name.' 退款。', [
                    'host_id' => $this->id,
                    'module_id' => $this->module_id,
                ]);
                $this->user->charge($refund, 'balance', '主机 '.$this->name.' 退款。', [
                    'host_id' => $this->id,
                    'module_id' => $this->module_id,
                ]);

                // 退款后，更新扣费时间
                $this->update([
                    'last_paid_at' => null,
                    'last_paid' => 0,
                ]);
            }
        }

        dispatch(new HostJob($this, 'delete'));

        return true;
    }

    public function getRefundAmount(): string|null
    {
        if (! $this->last_paid_at) {
            return null;
        }

        // 如果是月付，则按比例
        $days = $this->last_paid_at->daysInMonth;

        // 本月已经过的天数
        $passed_days = $this->last_paid_at->day;

        // 本月还剩下的天数
        $left_days = $days - $passed_days;

        // 计算
        return bcmul($this->last_paid, bcdiv($left_days, $days, 2), 2);
    }

    public function isTrial(): bool
    {
        return $this->trial_ends_at !== null;
    }

    public function isMonthly(): bool
    {
        return $this->billing_cycle === 'monthly';
    }

    public function isHourly(): bool
    {
        return $this->billing_cycle === 'hourly';
    }

    public function isNextMonthCancel(): bool
    {
        if ($this->isHourly()) {
            return false;
        }

        if ($this->isMonthly()) {
            return $this->cancel_at_period_end;
        }

        return false;
    }

    public function cost(
        string $amount = null, $auto = true, $description = null
    ): bool {
        // if ($this->isTrial() && $this->trial_ends_at->()) {
        //     return true;
        // }

        $this->load('user');
        $user = $this->user;
        $user->load('user_group');
        $user_group = $user->user_group;

        if ($user_group) {
            if ($user_group->exempt) {
                return true;
            }
        }

        $real_price = $amount ?? $this->price;

        if (! $amount) {
            if ($this->managed_price) {
                $real_price = $this->managed_price;
            }
        }

        $append_description = '';
        if ($user_group) {
            if ($user_group->discount !== 100 && $user_group->discount !== null) {
                $real_price = $user_group->getCostPrice($real_price);

                $append_description = ' (折扣 '.$user_group->discount.'%)';
            }
        }

        if ($auto && $this->isHourly()) {
            // 获取本月天数
            $days = now()->daysInMonth;
            // 本月每天的每小时的价格
            // 使用 bcmath 函数，解决浮点数计算精度问题
            $real_price = bcdiv($real_price, $days, 4);
            $real_price = bcdiv($real_price, 24, 4);
        }

        if ($real_price == 0) {
            echo '价格为 0，不扣费'.PHP_EOL;

            return true;
        }

        // 如果太小，则重置为 0.0001
        if ($real_price < 0.0001) {
            $real_price = 0.0001;
        }

        $real_price = bcdiv($real_price, 1, 4);

        $month = now()->month;

        $month_cache_key = 'user_'.$this->user_id.'_month_'.$month.'_hosts_balances';
        $hosts_balances = Cache::get($month_cache_key, []);

        // 统计 Host 消耗的 Balance
        if (isset($hosts_balances[$this->id])) {
            $hosts_balances[$this->id] += $real_price;
        } else {
            $hosts_balances[$this->id] = $real_price;
        }

        $hosts_balances[$this->id] = bcdiv($hosts_balances[$this->id], 1, 4);

        Cache::put($month_cache_key, $hosts_balances, 604800);

        $description = '主机: '.$this->name.', '.$description;

        if ($auto && $this->isHourly()) {
            $description .= '小时计费。';
        } elseif ($auto && $this->isMonthly()) {
            $description .= '月度计费。';
        } else {
            $description .= '扣费。';
        }

        if ($this->isTrial() && $this->trial_ends_at->isPast()) {
            $description .= '试用已过期。';
            $this->trial_ends_at = null;
        }

        if ($append_description) {
            $description .= $append_description;
        }

        $data = [
            'host_id' => $this->id,
            'module_id' => $this->module_id,
        ];

        $this->last_paid = $real_price;
        $this->last_paid_at = now();

        if ($this->isMonthly()) {
            $this->expired_at = now()->addMonth();
        }

        try {
            $left = $user->reduce($real_price, $description, ! $this->isHourly(), $data)->user_remain;
        } catch (BalanceNotEnoughException) {
            $this->changeStatus('suspended');

            return false;
        }

        $this->addLog($real_price);

        if ($left < 0) {
            $this->changeStatus('suspended');
        }

        $this->save();

        return true;
    }

    public function addLog(string $amount = '0'): bool
    {
        if ($amount === '0') {
            return false;
        }

        /** 统计收益开始 */
        $current_month = now()->month;
        $current_year = now()->year;

        $cache_key = 'module_earning_'.$this->module_id;

        // 应支付的提成
        $commission = config('settings.billing.commission');
        $should_amount = bcmul($amount, $commission, 4);

        // 应得的余额
        $should_balance = bcsub($amount, $should_amount, 4);
        // 如果太小，则重置为 0.0001
        if ($should_balance < 0.0001) {
            $should_balance = 0.0001;
        }

        $earnings = Cache::get($cache_key, []);

        if (! isset($earnings[$current_year])) {
            $earnings[$current_year] = [];
        }

        if (isset($earnings[$current_year][$current_month])) {
            $earnings[$current_year][$current_month]['balance'] = bcadd($earnings[$current_year][$current_month]['balance'], $amount, 4);
            $earnings[$current_year][$current_month]['should_balance'] = bcadd($earnings[$current_year][$current_month]['should_balance'], $should_balance, 4);
        } else {
            $earnings[$current_year][$current_month] = [
                'balance' => $amount,
                // 应得（交了手续费）
                'should_balance' => $should_balance,
            ];
        }

        // 删除 前 3 年的数据
        if (count($earnings) > 3) {
            $earnings = array_slice($earnings, -3, 3, true);
        }

        $this->module->charge($amount, 'balance', null);

        // 保存 1 年
        Cache::forever($cache_key, $earnings);

        /** 统计收益结束 */

        return true;
    }

    public function changeStatus(
        string $status
    ): bool {
        $user = auth()->guard('sanctum')->user() ?? auth()->guard('web')->user();

        if ($user) {
            if ($this->isPending() || $this->isOverdue() || $this->status === 'locked' || $this->status === 'unavailable') {
                return false;
            }

            if ($this->isMonthly()) {
                if (! $user->hasBalance($this->price)) {
                    return false;
                }
            } elseif (! $user->hasBalance('0.5')) {
                return false;
            }
        }

        if ($status === 'running') {
            return $this->run();
        } elseif (($status === 'suspended' || $status === 'suspend')) {
            return $this->suspend();
        } elseif ($status === 'stopped') {
            return $this->stop();
        }

        return false;
    }

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return now()->gt($this->next_due_at);
    }

    public function run(): bool
    {
        $this->update([
            'status' => 'running',
        ]);

        return true;
    }

    public function suspend(): bool
    {
        $this->update([
            'status' => 'suspended',
        ]);

        return true;
    }

    public function stop(): bool
    {
        $this->update([
            'status' => 'stopped',
        ]);

        return true;
    }

    public function updateOrDelete(): bool
    {
        dispatch(new UpdateOrDeleteHostJob($this));

        return true;
    }
}
