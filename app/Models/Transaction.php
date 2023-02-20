<?php

namespace App\Models;

use Carbon\Carbon;
use Jenssegers\Mongodb\Eloquent\Model;
use Symfony\Component\Uid\Ulid;

class Transaction extends Model
{
    const UPDATED_AT = null;

    protected $connection = 'mongodb';

    // 停用 updated_at
    protected $collection = 'transactions';

    protected $dates = [
        'created_at',
        'updated_at',
        'paid_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    protected $fillable = [
        // 类型
        'type',

        // 交易渠道
        'payment',

        // 描述
        'description',

        // 交易金额，负数则是扣除
        'amount',

        // 剩余余额
        'user_remain',
        'module_remain',

        // 赠送金额
        'gift',

        'user_id',
        'host_id',
        'module_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $transaction) {
            $user = null;
            $module = null;

            if ($transaction->user_id) {
                $user = (new User)->find($transaction->user_id);
            }

            if ($transaction->module_id) {
                $module = (new Module)->find($transaction->module_id);
            }

            if ($user) {
                $transaction->user_remain = $user->balance;
            }

            if ($module) {
                $transaction->module_remain = $module->balance;
            }

            $transaction->expired_at = Carbon::now()->addSeconds(7)->toString();
        });
    }

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
