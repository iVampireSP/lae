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
        'billing_cycle',
        'next_due_at',
        'suspended_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'managed_price' => 'decimal:2',
        'configuration' => 'array',
        'next_due_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    /** @noinspection PhpUndefinedMethodInspection */
    public function getUserHosts($user_id = null): array|Collection
    {
        return $this->where('user_id', $user_id)->with('module', function ($query) {
            $query->select(['id', 'name']);
        })->get();
    }

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsToAlias
    {
        return $this->belongsTo(Module::class);
    }

    // public function workOrders(): HasManyAlias
    // {
    //     return $this->hasMany(WorkOrder::class);
    // }

    public function getPrice(): float
    {
        return $this->managed_price ?? $this->price;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['running', 'stopped']);
    }

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

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function getNewDueDate(): string
    {
        $this->next_due_at = $this->next_due_at ?? now();

        return match ($this->billing_cycle) {
            'monthly' => $this->next_due_at->addMonth(),
            'quarterly' => $this->next_due_at->addMonths(3),
            'semi-annually' => $this->next_due_at->addMonths(6),
            'annually' => $this->next_due_at->addYear(),
            'biennially' => $this->next_due_at->addYears(2),
            'triennially' => $this->next_due_at->addYears(3),
            default => null,
        };
    }

    public function getRenewPrice(): string
    {
        return match ($this->billing_cycle) {
            'monthly' => $this->getPrice(),
            'quarterly' => bcmul($this->getPrice(), 3),
            'semi-annually' => bcmul($this->getPrice(), 6),
            'annually' => bcmul($this->getPrice(), 12),
            'biennially' => bcmul($this->getPrice(), 24),
            'triennially' => bcmul($this->getPrice(), 36),
            default => 0,
        };
    }

    public function renew(): bool
    {
        $price = $this->getRenewPrice();

        $description = '续费 '.$this->name.' 周期: '.$this->billing_cycle;

        try {
            $this->user->reduce($price, $description, true, [
                'host_id' => $this->id,
                'module_id' => $this->module_id,
            ]);
        } catch (BalanceNotEnoughException) {
            return false;
        }

        $this->addLog($price);

        $this->next_due_at = $this->getNewDueDate();

        $this->save();

        return true;
    }

    public function isOverdue(): bool
    {
        return now()->gt($this->next_due_at);
    }

    public function isCycle(): bool
    {
        return $this->billing_cycle !== null;
    }

    public function safeDelete(): bool
    {
        // 如果创建时间大于大于 1 小时
        if ($this->created_at->diffInHours(now()) > 1) {
            // 如果当前时间比扣费时间小，则说明没有扣费。执行扣费。
            if (now()->minute < $this->minute_at) {
                $this->cost();
            }
        }

        dispatch(new HostJob($this, 'delete'));

        return true;
    }

    public function cost(string $amount = null, $auto = true, $description = null): bool
    {
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

        if ($auto) {
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

        if (! $description) {
            $description = '模块发起的扣费。';
        }

        if ($auto) {
            $description = '自动扣费。';
        }

        if ($append_description) {
            $description .= $append_description;
        }

        $data = [
            'host_id' => $this->id,
            'module_id' => $this->module_id,
        ];

        $left = $user->reduce($real_price, $description, false, $data);

        $this->addLog($real_price);

        if ($left < 0) {
            $this->update([
                'status' => 'suspended',
            ]);
        }

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

    public function updateOrDelete(): bool
    {
        dispatch(new UpdateOrDeleteHostJob($this));

        return true;
    }
}
