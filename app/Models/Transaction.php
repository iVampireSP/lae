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

    const UPDATED_AT = null;
    protected $connection = 'mongodb';

    // 停用 updated_at
    protected $collection = 'transactions';
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

    public function increaseCurrentUserDrops($amount = 0)
    {
        return $this->increaseDrops(auth()->id(), $amount);
    }

    public function increaseDrops($user_id, $amount, $description = null, $payment = null)
    {
        $cache_key = 'user_drops_' . $user_id;

        $current_drops = Cache::get($cache_key, [
            'drops' => 0,
        ]);

        $current_drops['drops'] += $amount;

        Cache::forever($cache_key, $current_drops);

        $this->addIncomeDrops($user_id, $amount, $description, $payment);

        return $current_drops['drops'];
    }

    public function addIncomeDrops($user_id, $amount, $description, $payment = 'balance')
    {
        $data = [
            'type' => 'income',
            'payment' => $payment,
            'description' => $description,
            'income' => 0,
            'income_drops' => (float)$amount,
            'outcome' => 0,
            'outcome_drops' => 0,
        ];

        return $this->addLog($user_id, $data);
    }

    private function addLog($user_id, $data)
    {
        $user = User::find($user_id);


        $current = [
            'balance' => (float)$user->balance,
            'drops' => $this->getDrops($user_id),
            'user_id' => intval($user_id),
        ];

        // merge
        $data = array_merge($data, $current);

        // add expired at
        $data['expired_at'] = now()->addSeconds(7);

        return $this->create($data);
    }

    public function getDrops($user_id = null): float
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

    public function reduceDrops($user_id, $host_id, $module_id, $auto = 1, $amount = 0)
    {

        $cache_key = 'user_drops_' . $user_id;

        $current_drops = Cache::get($cache_key, [
            'drops' => 0,
        ]);

        $current_drops['drops'] = $current_drops['drops'] - $amount;

        $current_drops['drops'] = round($current_drops['drops'], 5);

        Cache::forever($cache_key, $current_drops);

        // if ($auto) {
        //     $description = '平台按时间自动扣费。';
        // } else {
        //     $description = '集成模块发起的扣费。';
        // }

        // $this->addPayoutDrops($user_id, $amount, $description, $host_id, $module_id);
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
            'income_drops' => 0,
            'outcome' => (float)$amount,
            'outcome_drops' => 0
        ];

        if ($module_id) {
            $data['module_id'] = $module_id;
        }

        return $this->addLog($user_id, $data);
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
            'income_drops' => 0,
            'outcome' => (float)$amount,
            'outcome_drops' => 0,
            'host_id' => $host_id,
            'module_id' => $module_id,
        ];

        return $this->addLog($user_id, $data);
    }

    /**
     * @throws ChargeException
     */
    public function addAmount($user_id, $payment = 'console', $amount = 0, $description = null, $add_to_balances = false)
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

            if ($add_to_balances) {
                $data = [
                    'user_id' => $user_id,
                    'amount' => $amount,
                    'payment' => $payment,
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
            'income_drops' => 0,
            'outcome' => 0,
            'outcome_drops' => 0,
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

    public function transferDrops(User $user, User $to, float $amount, string|null $description = null): bool
    {

        $user_drops = $this->getDrops($user->id);

        // if drops not enough
        if ($user_drops < $amount) {
            return false;
        }

        $description_new = "转账给 {$to->name}($to->email)  {$amount} Drops， $description";

        $this->reduceDropsWithoutHost($user->id, $amount, $description_new);

        $description_new = "收到来自 {$to->name}($to->email) 转来的 {$amount} Drops， $description";


        $this->increaseDrops($to->id, $amount, $description_new, 'transfer');

        return true;
    }

    public function reduceDropsWithoutHost($user_id, $amount = 0, $description = null)
    {

        $cache_key = 'user_drops_' . $user_id;

        $current_drops = Cache::get($cache_key, [
            'drops' => 0,
        ]);

        $current_drops['drops'] = $current_drops['drops'] - $amount;

        $current_drops['drops'] = round($current_drops['drops'], 5);

        Cache::forever($cache_key, $current_drops);

        $this->addPayoutDrops($user_id, $amount, $description, null, null);
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
            'outcome_drops' => (float)$amount,
            'host_id' => $host_id,
            'module_id' => $module_id,
        ];


        // $amount = (double) $amount;

        // Log::debug($amount);

        // $month = now()->month;

        // Cache::increment('user_' . $user_id . '_month_' . $month . '_drops', $amount);

        return $this->addLog($user_id, $data);
    }
}
