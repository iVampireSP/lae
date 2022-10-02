<?php

namespace App\Models;

use Jenssegers\Mongodb\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Transaction extends Model
{
    // $t = (new App\Models\Transaction)->create(['name' => 1])

    protected $connection = 'mongodb';
    protected $collection = 'transactions';

    // 停用 updated_at
    const UPDATED_AT = null;

    protected $dates = [
        'created_at',
        'updated_at',
        'paid_at',
    ];

    protected $fillable = [
        // 交易类型
        'type',

        // 交易渠道
        'payment',

        // 描述
        'description',

        // 入账
        'income',

        // 入账 Drops
        'income_drops',

        // 出账
        'outcome',
        // 出账 Drops
        'outcome_drops',

        // 可用余额
        'balance',

        // 可用 Drops
        'drops',

        // 赠送金额
        'gift',

        // 赠送 Drops
        'gift_drops',

        'user_id',
        'host_id',
        'module_id',
    ];


    // scope this user
    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }


    public function getDrops($user_id = null)
    {
        //
        if (!$user_id) {
            $user_id = auth()->id();
        }

        $cache_key = 'user_drops_' . $user_id;

        $decimal = config('drops.decimal');

        $drops = Cache::get($cache_key);

        $drops = $drops / $decimal;

        return $drops;
    }

    public function increaseCurrentUserDrops($amount = 0)
    {
        return $this->increaseDrops(auth()->id(), $amount);
    }


    public function increaseDrops($user_id, $amount = 0)
    {
        $cache_key = 'user_drops_' . $user_id;

        $decimal = config('drops.decimal');


        $amount = $amount * $decimal;

        $drops = Cache::increment($cache_key, $amount);

        return $drops;
    }


    public function reduceDrops($user_id, $host_id, $module_id, $auto = 1, $amount = 0)
    {

        $cache_key = 'user_drops_' . $user_id;

        $decimal = config('drops.decimal');

        $month = now()->month;

        Cache::increment('user_' . $user_id . '_month_' . $month . '_drops', $amount);

        $amount = $amount * $decimal;

        Cache::decrement($cache_key, $amount);

        if ($auto) {
            $description = '平台按时间自动扣费。';
        } else {
            $description = '集成模块发起的扣费。';
        }

        $this->addPayoutDrops($user_id, $amount / $decimal, $description, $host_id, $module_id);
    }


    public function addPayoutDrops($user_id, $amount, $description, $host_id, $module_id)
    {
        $data = [
            'type' => 'payout',
            'payment' => 'drops',
            'description' => $description,
            'income' => 0,
            'income_drops' => 0,
            'outcome' => 0,
            'outcome_drops' => $amount,
            'host_id' => $host_id,
            'module_id' => $module_id,
        ];

        return $this->addLog($user_id, $data);
    }


    public function addIncomeDrops($user_id, $amount, $description)
    {
        $data = [
            'type' => 'income',
            'payment' => 'balance',
            'description' => $description,
            'income' => 0,
            'income_drops' => $amount,
            'outcome' => 0,
            'outcome_drops' => 0,
        ];

        return $this->addLog($user_id, $data);
    }

    public function addIncomeBalance($user_id, $payment, $amount, $description)
    {
        $data = [
            'type' => 'income',
            'payment' => $payment,
            'description' => $description,
            'income' => $amount,
            'income_drops' => 0,
            'outcome' => 0,
            'outcome_drops' => 0,
        ];

        return $this->addLog($user_id, $data);
    }

    public function addPayoutBalance($user_id, $amount, $description)
    {
        $data = [
            'type' => 'payout',
            'payment' => 'balance',
            'description' => $description,
            'income' => 0,
            'income_drops' => 0,
            'outcome' => $amount,
            'outcome_drops' => 0
        ];

        return $this->addLog($user_id, $data);
    }

    public function reduceAmount($user_id, $amount = 0, $description = '扣除费用请求。')
    {
        $user = User::findOrFail($user_id);

        if ($user) {
            $user->balance -= $amount;

            $user->save();
        }

        $data = [
            'type' => 'payout',
            'payment' => 'balance',
            'description' => $description,
            'income' => 0,
            'income_drops' => 0,
            'outcome' => $amount,
            'outcome_drops' => 0
        ];

        return $this->addLog($user_id, $data);
    }


    private function addLog($user_id, $data)
    {
        $user = User::find($user_id);

        $current = [
            'balance' => $user->balance,
            'drops' => $this->getDrops($user_id),
            'user_id' => intval($user_id),
        ];

        // merge
        $data = array_merge($data, $current);

        // add expired at
        $data['expired_at'] = now()->addSeconds(7);


        return $this->create($data);
    }
}
