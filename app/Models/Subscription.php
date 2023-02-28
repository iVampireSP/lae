<?php

namespace App\Models;

use App\Exceptions\User\BalanceNotEnoughException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    public $fillable = [
        'name',
        'status',
        'plan_id',
        'configuration',
        'price',
        'expired_at',
        'trial_ends_at',
        'module_id',
        'user_id',
        'cancel_at_period_end',
        'renew_price',
    ];

    protected $casts = [
        'configuration' => 'array',
        'price' => 'decimal:2',
        'renew_price' => 'decimal:2',
    ];

    protected $dates = [
        'expired_at',
        'trial_ends_at',
    ];

    public function scopeThisUser($query, $module_id = null)
    {
        $query = $query->where('user_id', auth()->id());

        if ($module_id) {
            $query = $query->where('module_id', $module_id);
        }

        return $query;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function active(): bool
    {
        return $this->canActivate() && $this->renew();
    }

    public function canActivate(bool $ignore_activated = false): bool
    {
        if ($ignore_activated && $this->isActive()) {
            return true;
        }

        // 检测 trial_ends_at 是否过期
        if ($this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function renew(): bool
    {
        if ($this->isTrial()) {
            $this->status = 'active';
        }

        // 如果过期时间距离今天超过了 7 天，那么就不能续费了
        if ($this->expired_at && $this->expired_at->diffInDays(now()) > 7) {
            return false;
        }

        $price = $this->price;

        try {
            $this->user->reduce($price, '订阅: '.$this->name, true, [
                'module_id' => $this->module_id,
                'user_id' => $this->user_id,
                'subscription_id' => $this->id,
            ]);
            $this->module->charge($price, 'module_balance', '订阅: '.$this->name.' 续费', [
                'module_id' => $this->module_id,
                'user_id' => $this->user_id,
                'subscription_id' => $this->id,
            ]);
        } catch (BalanceNotEnoughException) {
            return false;
        }

        $this->renew_price = $price;

        if (! $this->isTrial()) {
            $this->expired_at = now()->addMonth();
        }

        $this->status = 'active';
        $this->save();

        return true;
    }

    public function isTrial(): bool
    {
        return $this->trial_ends_at !== null;
    }

    public function safeDelete()
    {
        // 如果是试用，那么就直接删除
        if ($this->isTrial() || $this->isExpired() || $this->isDraft() || ! $this->renew_price) {
            $this->delete();

            return;
        }

        // 如果是正式订阅，那么就按使用天数计算退款
        $days = $this->expired_at ? $this->expired_at->diffInDays(now()) : 27;
        // 获取 create_at 当时的月份的天数
        $daysInMonth = $this->created_at->daysInMonth;

        // 按照使用天数计算退款(bcdiv 保留两位小数)
        $refund = bcdiv($this->renew_price, $daysInMonth, 2) * $days;

        // 如果退款金额大于 0，那么就退款
        if ($refund > 0) {
            $this->user->charge($refund, 'balance', '订阅: '.$this->name.' 退款', [
                'module_id' => $this->module_id,
                'user_id' => $this->user_id,
                'subscription_id' => $this->id,
            ]);
            $this->module->reduce($refund, '订阅: '.$this->name.' 退款', false, [
                'module_id' => $this->module_id,
                'user_id' => $this->user_id,
                'subscription_id' => $this->id,
            ]);
        }

        $this->delete();
    }

    public function isExpired(): bool
    {
        return $this->expired_at && $this->expired_at->isPast();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
