<?php

namespace App\Models;

use App\Exceptions\ChargeException;
use App\Exceptions\User\BalanceNotEnoughException;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Jenssegers\Mongodb\Eloquent\Model;

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
        // 交易类型
        'type',

        // 交易渠道
        'payment',

        // 描述
        'description',

        // 入账
        'income',

        // 出账
        'outcome',

        // 可用余额
        'balances',
        'balance',

        // 赠送金额
        'gift',

        'user_id',
        'host_id',
        'module_id',
    ];

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
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
        } finally {
            optional($lock)->release();
        }

        return $user->balance;
    }

    public function addPayoutBalance($user_id, $amount, $description, $module_id = null)
    {
        $data = [
            'type' => 'payout',
            'payment' => 'balance',
            'description' => $description,
            'income' => 0,
            'outcome' => (float)$amount,
        ];

        if ($module_id) {
            $data['module_id'] = $module_id;
        }

        return $this->addLog($user_id, $data);
    }

    private function addLog($user_id, $data)
    {
        $user = User::find($user_id);

        $current = [
            'balance' => (float)$user->balance,
            'user_id' => intval($user_id),
        ];

        // merge
        $data = array_merge($data, $current);

        // add expired at
        $data['expired_at'] = now()->addSeconds(7);

        return $this->create($data);
    }

    public function reduceAmountModuleFail($user_id, $module_id, $amount = 0, $description = '扣除费用请求。')
    {

        $lock = Cache::lock("user_balance_lock_" . $user_id, 10);
        try {

            $lock->block(5);

            $user = User::findOrFail($user_id);

            $user->balance -= $amount;

            // if balance < 0
            if ($user->balance < 0) {
                throw new BalanceNotEnoughException('余额不足。');
            }

            $user->save();

            $this->addPayoutBalance($user_id, $amount, $description, $module_id);
        } finally {
            optional($lock)->release();
        }

        return $user->balance;
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
        } finally {
            optional($lock)->release();
        }

        return $user->balance;
    }

    public function addHostPayoutBalance($user_id, $host_id, $module_id, $amount, $description)
    {
        $data = [
            'type' => 'payout',
            'payment' => 'balance',
            'description' => $description,
            'income' => 0,
            'outcome' => (float)$amount,
            'host_id' => $host_id,
            'module_id' => $module_id,
        ];

        return $this->addLog($user_id, $data);
    }

    /**
     * @throws ChargeException
     */
    public function addAmount($user_id, $payment = 'console', $amount = 0, $description = null, $add_charge_log = false)
    {
        $lock = Cache::lock("user_balance_lock_" . $user_id, 10);
        try {

            $lock->block(5);

            $user = User::findOrFail($user_id);

            $left_balance = $user->balance + $amount;

            $user->increment('balance', $amount);

            if (!$description) {
                $description = '充值 ' . $amount . ' 元';
            }

            if ($add_charge_log) {
                $data = [
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'payment' => $payment,
                    'paid_at' => Carbon::now(),
                ];

                Balance::create($data);
            }

            $this->addIncomeBalance($user_id, $payment, $amount, $description);
        } catch (LockTimeoutException $e) {
            Log::error($e);
            throw new ChargeException('充值失败，请稍后再试。');
        } finally {
            optional($lock)->release();
        }

        return $left_balance;
    }

    public function addIncomeBalance($user_id, $payment, $amount, $description)
    {
        $data = [
            'type' => 'income',
            'payment' => $payment,
            'description' => $description,
            'income' => (float)$amount,
            'outcome' => 0,
        ];

        return $this->addLog($user_id, $data);
    }

    public function transfer(User $user, User $to, float $amount, string|null $description): float
    {
        $lock = Cache::lock("user_balance_lock_" . $user->id, 10);
        $lock_to = Cache::lock("user_balance_lock_" . $to->id, 10);
        try {

            $lock->block(5);
            $lock_to->block(5);

            $user->balance -= $amount;
            $user->save();

            $to->balance += $amount;
            $to->save();

            if (!$description) {
                $description = '完成。';
            }

            $description_new = "转账给 {$to->name}({$to->email}) {$amount} 元，{$description}";

            $this->addPayoutBalance($user->id, $amount, $description_new);

            $description_new = "收到来自 {$user->name}($user->email) 转来的 {$amount} 元， $description";

            $this->addIncomeBalance($to->id, 'transfer', $amount, $description_new);
        } finally {
            optional($lock)->release();
            optional($lock_to)->release();
        }

        return $user->balance;
    }

}
