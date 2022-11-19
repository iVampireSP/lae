<?php

namespace App\Models;

use App\Events\UserEvent;
use App\Exceptions\User\BalanceNotEnoughException;
use App\Models\WorkOrder\WorkOrder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Database\Eloquent\Relations\HasMany as HasManyAlias;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

// use Illuminate\Database\Eloquent\SoftDeletes;

class Host extends Model
{
    use HasFactory, Cachable;

    protected $table = 'hosts';

    protected $fillable = [
        'name',
        'module_id',
        'user_id',
        'price',
        'configuration',
        'status',
        'managed_price',
        'suspended_at',
    ];

    protected $casts = [
        // 'configuration' => 'array',
        'suspended_at' => 'datetime',

    ];


    // user

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            broadcast(new UserEvent($model->user_id, 'hosts.created', $model));
        });

        static::updating(function ($model) {

            if ($model->isDirty('status')) {
                if ($model->status == 'suspended') {
                    $model->suspended_at = now();
                } else {
                    $model->suspended_at = null;
                }
            }

            broadcast(new UserEvent($model->user_id, 'hosts.updating', $model));
        });

        // when Updated
        static::updated(function ($model) {
            dispatch(new \App\Jobs\Module\Host($model, 'patch'));

            Cache::forget('user_hosts_' . $model->user_id);
            Cache::forget('user_tasks_' . $model->user_id);


            broadcast(new UserEvent($model->user_id, 'hosts.updated', $model));
        });

        //
        // static::deleting(function ($model) {
        //     broadcast(new UserEvent($model->user_id, 'hosts.deleting', $model));
        // });

        static::deleting(function ($model) {
            Cache::forget('user_tasks_' . $model->user_id);
        });

        static::deleted(function ($model) {
            broadcast(new UserEvent($model->user_id, 'hosts.deleted', $model));
            Cache::forget('user_tasks_' . $model->user_id);
            Cache::forget('user_hosts_' . $model->user_id);
        });
    }

    // module

    public function getUserHosts($user_id = null)
    {
        return $this->where('user_id', $user_id)->with('module', function ($query) {
            $query->select(['id', 'name']);
        })->get();
    }

    // workOrders

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }

    // scope

    public function module(): BelongsToAlias
    {
        return $this->belongsTo(Module::class);
    }

    public function workOrders(): HasManyAlias
    {
        return $this->hasMany(WorkOrder::class);
    }


    // cost

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['running', 'stopped'])->where('price', '!=', 0)->orWhereNotNull('managed_price');
    }

    public function scopeThisUser($query, $module = null)
    {
        if ($module) {
            return $query->where('user_id', auth()->id())->where('module_id', $module);
        } else {
            return $query->where('user_id', auth()->id());
        }
    }

    public function safeDelete(): bool
    {
        dispatch(new \App\Jobs\Module\Host($this, 'delete'));
        return true;
    }

    public function cost($price = null, $auto = true): bool
    {

        Log::debug('Host::cost()');
        $this->load('user');

        $transaction = new Transaction();

        $drops = $transaction->getDrops($this->user_id);

        $real_price = $price ?? $this->price;

        if (!$price) {
            $real_price = $this->managed_price;
        }


        if ($real_price == 0) {
            return true;
        }

        $real_price = round($real_price ?? 0, 8);

        $amount = $price / config('drops.rate') + 1;

        // if drops <= price
        if ($drops < $real_price) {
            try {
                // 算出需要补充多少 Drops
                $need = $real_price - $drops;

                // 算出需要补充多少余额
                $need_amount = $need / config('drops.rate') + 1;

                $this->user->toDrops($amount + $need_amount);
            } catch (BalanceNotEnoughException) {
                $this->update([
                    'status' => 'suspended',
                ]);

                return false;
            }
        } else if ($this->status == 'suspended') {
            $this->update([
                'status' => 'stopped',
            ]);
        }

        $month = now()->month;

        $month_cache_key = 'user_' . $this->user_id . '_month_' . $month . '_hosts_drops';
        $hosts_drops = Cache::get($month_cache_key, []);

        // 统计 Host 消耗的 Drops
        if (isset($hosts_drops[$this->id])) {
            $hosts_drops[$this->id] += $real_price;
        } else {
            $hosts_drops[$this->id] = $real_price;
        }

        Cache::put($month_cache_key, $hosts_drops, 604800);

        $transaction->reduceDrops($this->user_id, $this->id, $this->module_id, $auto, $real_price);

        $this->addLog('drops', $real_price);

        broadcast(new UserEvent($this->user_id, 'balances.drops.reduced', $this->user));

        // 检测用户余额是否足够
        if ($this->user->balance < 0) {
            $this->update([
                'status' => 'suspended',
            ]);
        }

        return true;
    }

    public function addLog($type = 'drops', float $amount = 0): bool
    {
        if ($amount == 0) {
            return false;
        }

        /** 统计收益开始 */
        $current_month = now()->month;
        $current_year = now()->year;

        $cache_key = 'module_earning_' . $this->module_id;


        $rate = (int)config('drops.rate');
        $commission = (float)config('drops.commission');


        if ($type == 'drops') {
            // 换成 余额

            $amount = $amount / $rate;
        }

        // $amount = round($amount, 2);
        // Log::debug('addLog', [
        //     'amount' => $amount,
        //     'rate' => $rate,
        //     'commission' => $commission,
        // ]);


        $should_amount = round($amount * $commission, 2);

        // 应得的余额
        $should_balance = $amount - $should_amount;

        $earnings = Cache::get($cache_key, []);

        if (!isset($earnings[$current_year])) {
            $earnings[$current_year] = [];
        }

        if ($type == 'drops') {
            $drops = $amount;
        } else {
            $drops = 0;
        }


        if (isset($earnings[$current_year][$current_month])) {
            $earnings[$current_year][$current_month]['balance'] += $amount;
            $earnings[$current_year][$current_month]['should_balance'] += $should_balance;
            $earnings[$current_year][$current_month]['drops'] += $drops;
        } else {

            $earnings[$current_year][$current_month] = [
                'balance' => $amount,
                // 应得（交了手续费）
                'should_balance' => $should_balance,
                'drops' => $drops
            ];
        }

        // 删除 前 3 年的数据
        if (count($earnings) > 3) {
            $earnings = array_slice($earnings, -3, 3, true);

        }

        // 保存 1 年
        Cache::put($cache_key, $earnings, 24 * 60 * 60 * 365);

        /** 统计收益结束 */

        return true;
    }

    public function costBalance($amount = 1): bool
    {
        $transaction = new Transaction();

        $month = now()->month;

        $month_cache_key = 'user_' . $this->user_id . '_month_' . $month . '_hosts_balances';
        $hosts_drops = Cache::get($month_cache_key, []);

        // 统计 Host 消耗的 Drops
        if (isset($hosts_drops[$this->id])) {
            $hosts_drops[$this->id] += $amount;
        } else {
            $hosts_drops[$this->id] = $amount;
        }

        Cache::put($month_cache_key, $hosts_drops, 604800);

        $left = $transaction->reduceHostAmount($this->user_id, $this->id, $this->module_id, $amount);

        $this->addLog('balance', $amount);


        broadcast(new UserEvent($this->user_id, 'balances.amount.reduced', $this->user));

        if ($left < 0) {
            $this->update([
                'status' => 'suspended',
            ]);
        }

        return true;
    }
}
