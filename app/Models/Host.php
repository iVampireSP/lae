<?php

namespace App\Models;

use App\Events\UserEvent;
use App\Models\WorkOrder\WorkOrder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Support\Facades\Cache;

/**
 * App\Models\Host
 *
 * @property int                             $id
 * @property string                          $name
 * @property string                          $module_id
 * @property int                             $user_id
 * @property float                           $price
 * @property float|null                      $managed_price
 * @property mixed|null                      $configuration
 * @property string                          $status
 * @property int|null                        $hour
 * @property \Illuminate\Support\Carbon|null $suspended_at
 * @property string|null                     $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Module         $module
 * @property-read \App\Models\User           $user
 * @property-read Collection|WorkOrder[]     $workOrders
 * @property-read int|null                   $work_orders_count
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host active()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host thisUser($module = null)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereConfiguration($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereDeletedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereHour($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereManagedPrice($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereModuleId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereName($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host wherePrice($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereStatus($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereSuspendedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->hour_at = now()->hour;
            $model->minute_at = now()->minute_at;

            if ($model->price !== null) {
                $model->price = round($model->price, 2);
            }

            if ($model->managed_price !== null) {
                $model->managed_price = round($model->managed_price, 2);
            }
        });

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

            if ($model->isDirty('price')) {
                $model->price = round($model->price, 2);
            }

            if ($model->isDirty('managed_price') && $model->managed_price !== null) {
                $model->managed_price = round($model->managed_price, 2);
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


    public function getPrice() {
        return $this->managed_price ?? $this->price;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['running', 'stopped']);
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
        // 如果创建时间大于大于 1 小时
        if ($this->created_at->diffInHours(now()) > 1) {
            // 如果当前时间比扣费时间小，则说明没有扣费。执行扣费。
            if (now()->minute < $this->minute_at) {
                $this->cost();
            }
        }

        dispatch(new \App\Jobs\Module\Host($this, 'delete'));
        return true;
    }

    // public function cost($price = null, $auto = true): bool
    // {
    //     $this->load('user');
    //
    //     $transaction = new Transaction();
    //
    //     $drops = $transaction->getDrops($this->user_id);
    //
    //     $real_price = $price ?? $this->price;
    //
    //     if (!$price) {
    //
    //         if ($this->managed_price) {
    //             $real_price = $this->managed_price;
    //         }
    //     }
    //
    //     if ($real_price == 0) {
    //         return true;
    //     }
    //
    //     $real_price = round($real_price ?? 0, 8);
    //
    //     $amount = $price / config('drops.rate') + 1;
    //
    //     // if drops <= price
    //     if ($drops < $real_price) {
    //         try {
    //             // 算出需要补充多少 Drops
    //             $need = $real_price - $drops;
    //
    //             // 算出需要补充多少余额
    //             $need_amount = $need / config('drops.rate') + 1;
    //
    //             $this->user->toDrops($amount + $need_amount);
    //         } catch (BalanceNotEnoughException) {
    //             $this->update([
    //                 'status' => 'suspended',
    //             ]);
    //
    //             return false;
    //         }
    //     } else if ($this->status == 'suspended') {
    //         $this->update([
    //             'status' => 'stopped',
    //         ]);
    //     }
    //
    //     $month = now()->month;
    //
    //     $month_cache_key = 'user_' . $this->user_id . '_month_' . $month . '_hosts_drops';
    //     $hosts_drops = Cache::get($month_cache_key, []);
    //
    //     // 统计 Host 消耗的 Drops
    //     if (isset($hosts_drops[$this->id])) {
    //         $hosts_drops[$this->id] += $real_price;
    //     } else {
    //         $hosts_drops[$this->id] = $real_price;
    //     }
    //
    //     Cache::put($month_cache_key, $hosts_drops, 604800);
    //
    //     $transaction->reduceDrops($this->user_id, $this->id, $this->module_id, $auto, $real_price);
    //
    //     $this->addLog('drops', $real_price);
    //
    //     broadcast(new UserEvent($this->user_id, 'balances.drops.reduced', $this->user));
    //
    //     // 检测用户余额是否足够
    //     if ($this->user->balance < 0) {
    //         $this->update([
    //             'status' => 'suspended',
    //         ]);
    //     }
    //
    //     return true;
    // }

    public function cost($amount = null, $auto = true): bool
    {
        $this->load('user');

        $real_price = $amount ?? $this->price;

        if (!$amount) {
            if ($this->managed_price) {
                $real_price = $this->managed_price;
            }
        }

        if ($auto) {
            // 获取本月天数
            $days = now()->daysInMonth;
            // 本月每天的每小时的价格
            $real_price = $real_price / $days / 24;
        }

        if ($real_price == 0) {
            return true;
        }

        $real_price = round($real_price ?? 0, 8);

        $transaction = new Transaction();

        $month = now()->month;

        $month_cache_key = 'user_' . $this->user_id . '_month_' . $month . '_hosts_balances';
        $hosts_balances = Cache::get($month_cache_key, []);

        // 统计 Host 消耗的 Balance
        if (isset($hosts_balances[$this->id])) {
            $hosts_balances[$this->id] += $real_price;
        } else {
            $hosts_balances[$this->id] = $real_price;
        }

        $hosts_balances[$this->id] = round($hosts_balances[$this->id], 8);

        Cache::put($month_cache_key, $hosts_balances, 604800);

        $description = '模块发起的扣费。';

        if ($auto) {
            $description = '自动扣费。';
        }

        $left = $transaction->reduceHostAmount($this->user_id, $this->id, $this->module_id, $real_price, $description);

        $this->addLog($real_price);

        broadcast(new UserEvent($this->user_id, 'balances.amount.reduced', $this->user));

        if ($left < 0) {
            $this->update([
                'status' => 'suspended',
            ]);
        }

        return true;
    }

    public function addLog(float|null $amount = 0): bool
    {
        if ($amount === 0 || $amount === null) {
            return false;
        }

        /** 统计收益开始 */
        $current_month = now()->month;
        $current_year = now()->year;

        $cache_key = 'module_earning_' . $this->module_id;

        $commission = (float)config('billing.commission');

        $should_amount = round($amount * $commission, 2);

        // 应得的余额
        $should_balance = $amount - $should_amount;

        $earnings = Cache::get($cache_key, []);

        if (!isset($earnings[$current_year])) {
            $earnings[$current_year] = [];
        }

        if (isset($earnings[$current_year][$current_month])) {
            $earnings[$current_year][$current_month]['balance'] += $amount;
            $earnings[$current_year][$current_month]['should_balance'] += $should_balance;
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

        // 保存 1 年
        Cache::put($cache_key, $earnings, 24 * 60 * 60 * 365);

        /** 统计收益结束 */

        return true;
    }
}
