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

        // 计算需要乘以多少
        $multiple = 1;
        for ($i = 0; $i < $decimal; $i++) {
            $multiple *= 10;
        }

        $drops = Cache::get($cache_key);

        // 除以 $multiple
        $drops = $drops / $multiple;

        return $drops;
    }


    public function reduceCurrentUserDrops($amount = 0)
    {
        return $this->reduceDrops(auth()->id(), $amount);
    }

    public function increaseCurrentUserDrops($amount = 0)
    {
        return $this->increaseDrops(auth()->id(), $amount);
    }


    public function increaseDrops($user_id, $amount = 0)
    {
        $cache_key = 'user_drops_' . $user_id;

        $decimal = config('drops.decimal');

        // 计算需要乘以多少
        $multiple = 1;
        for ($i = 0; $i < $decimal; $i++) {
            $multiple *= 10;
        }

        $amount = $amount * $multiple;

        $drops = Cache::increment($cache_key, $amount);


        return $drops;
    }


    public function reduceDrops($user_id, $amount = 0, $description = null)
    {
        $cache_key = 'user_drops_' . $user_id;

        $decimal = config('drops.decimal');

        // 计算需要乘以多少
        $multiple = 1;
        for ($i = 0; $i < $decimal; $i++) {
            $multiple *= 10;
        }

        $month = now()->month;

        Cache::increment('user_' . $user_id . '_month_' . $month . '_drops', $amount);

        $amount = $amount * $multiple;

        $drops = Cache::decrement($cache_key, $amount);

        // (new App\Models\Transaction)->create(['name' => 1]);

        $this->addPayoutDrops($user_id, $amount, $description);

        return $drops;
    }


    public function addPayoutDrops($user_id, $amount, $description)
    {
        $data = [
            'type' => 'payout',
            'payment' => 'drops',
            'description' => $description,
            'income' => 0,
            'income_drops' => 0,
            'outcome' => 0,
            'outcome_drops' => $amount,
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
            'outcome_drops' => 0,
        ];

        return $this->addLog($user_id, $data);
    }


    private function addLog($user_id, $data)
    {
        $user = User::find($user_id);


        $current = [
            'balance' => $user->balance,
            'drops' => $this->getDrops($user_id),
            'user_id' => $user_id,
        ];

        // merge
        $data = array_merge($data, $current);

        // add expired at
        $data['expired_at'] = now()->addSeconds(7);


        return $this->create($data);
    }
}
