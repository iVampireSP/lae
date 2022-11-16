<?php

namespace App\Models;

use App\Exceptions\ChargeException;
use App\Exceptions\User\BalanceNotEnoughException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Jenssegers\Mongodb\Eloquent\Model;

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
        'balances',

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

        $drops = Cache::get($cache_key, [
            'drops' => 0,
        ]);

        // 保留 8 位
        $drops['drops'] = round($drops['drops'], 8);

        return $drops['drops'];
    }

    public function increaseCurrentUserDrops($amount = 0)
    {
        return $this->increaseDrops(auth()->id(), $amount);
    }


    public function increaseDrops($user_id, $amount = 0)
    {
        $cache_key = 'user_drops_' . $user_id;

        $current_drops = Cache::get($cache_key, [
            'drops' => 0,
        ]);

        $current_drops['drops'] += $amount;

        Cache::forever($cache_key, $current_drops);

        return $current_drops['drops'];
    }


    public function reduceDrops($user_id, $host_id, $module_id, $auto = 1, $amount = 0)
    {

        $cache_key = 'user_drops_' . $user_id;

        $current_drops = Cache::get($cache_key, [
            'drops' => 0,
        ]);

        $current_drops['drops'] = $current_drops['drops'] - $amount;

        $current_drops['drops'] = round($current_drops['drops'], 5);

        Cache::forever($cache_key, $current_drops);

        if ($auto) {
            $description = '平台按时间自动扣费。';
        } else {
            $description = '集成模块发起的扣费。';
        }

        $this->addPayoutDrops($user_id, $amount, $description, $host_id, $module_id);
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
            'outcome_drops' => (float) $amount,
            'host_id' => $host_id,
            'module_id' => $module_id,
        ];


        // $amount = (double) $amount;

        // Log::debug($amount);

        // $month = now()->month;

        // Cache::increment('user_' . $user_id . '_month_' . $month . '_drops', $amount);

        return $this->addLog($user_id, $data);
    }


    public function addIncomeDrops($user_id, $amount, $description)
    {
        $data = [
            'type' => 'income',
            'payment' => 'balances',
            'description' => $description,
            'income' => 0,
            'income_drops' => (float) $amount,
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
            'income' => (float) $amount,
            'income_drops' => 0,
            'outcome' => 0,
            'outcome_drops' => 0,
        ];

        return $this->addLog($user_id, $data);
    }

    public function addPayoutBalance($user_id, $amount, $description, $module_id = null)
    {
        $data = [
            'type' => 'payout',
            'payment' => 'balances',
            'description' => $description,
            'income' => 0,
            'income_drops' => 0,
            'outcome' => (float) $amount,
            'outcome_drops' => 0
        ];

        if ($module_id) {
            $data['module_id'] = $module_id;
        }

        return $this->addLog($user_id, $data);
    }

    public function addHostPayoutBalance($user_id, $host_id, $module_id, $amount, $description)
    {
        $data = [
            'type' => 'payout',
            'payment' => 'balances',
            'description' => $description,
            'income' => 0,
            'income_drops' => 0,
            'outcome' => (float) $amount,
            'outcome_drops' => 0,
            'host_id' => $host_id,
            'module_id' => $module_id,
        ];

        return $this->addLog($user_id, $data);
    }



    public function reduceAmount($user_id, $amount = 0, $description = '扣除费用请求。')
    {

        $lock = Cache::lock("user_balance_lock_" . $user_id, 10);
        try {

            $lock->block(5);

            $user = User::findOrFail($user_id);

            $user->balance -= $amount;
            $user->save();

            $this->addPayoutBalance($user_id, $amount, $description);

            return $user->balance;
        } finally {
            optional($lock)->release();
        }

        return false;
    }

    public function reduceAmountModuleFail($user_id, $module_id, $amount = 0, $description = '扣除费用请求。')
    {

        $lock = Cache::lock("user_balance_lock_" . $user_id, 10);
        try {

            $lock->block(5);

            $user = User::findOrFail($user_id);

            $user->balance -= $amount;

            // if balances < 0
            if ($user->balance < 0) {
                throw new BalanceNotEnoughException('余额不足。');
            }

            $user->save();

            $this->addPayoutBalance($user_id, $amount, $description, $module_id);

            return $user->balance;
        } finally {
            optional($lock)->release();
        }

        return false;
    }


    public function reduceHostAmount($user_id, $host_id, $module_id, $amount = 0, $description = '扣除费用请求。')
    {

        $lock = Cache::lock("user_balance_lock_" . $user_id, 10);
        try {

            $lock->block(5);

            $user = User::findOrFail($user_id);

            $user->balance -= $amount;
            $user->save();

            $this->addHostPayoutBalance($user_id, $host_id, $module_id, $amount, $description);

            return $user->balance;
        } finally {
            optional($lock)->release();
        }

        return false;
    }

    public function addAmount($user_id, $payment = 'console', $amount = 0, $description = null)
    {
        $lock = Cache::lock("user_balance_lock_" . $user_id, 10);
        try {

            $lock->block(5);

            $user = User::findOrFail($user_id);

            $left_balance = $user->balance + $amount;

            $user->increment('balance', $amount);

            if (!$description) {
                $description = '充值金额。';
            } else {
                $description = '充值 ' . $amount . ' 元';
            }

            $this->addIncomeBalance($user_id, $payment, $amount, $description);

            return $left_balance;
        } catch (LockTimeoutException $e) {
            Log::error($e);
            throw new ChargeException('充值失败，请稍后再试。');
        } finally {
            optional($lock)->release();
        }

        return false;
    }


    private function addLog($user_id, $data)
    {
        $user = User::find($user_id);

        $current = [
            'balances' => $user->balance,
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
