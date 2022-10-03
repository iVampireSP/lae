<?php

namespace App\Models;

use App\Events\UserEvent;
use App\Models\Transaction;
use App\Models\Module\Module;
// use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\User\BalanceNotEnoughException;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Host extends Model
{
    use HasFactory;

    protected $table = 'hosts';

    protected $fillable = [
        'name',
        'module_id',
        'user_id',
        'price',
        'configuration',
        'status',
        'managed_price',
    ];

    protected $casts = [
        // 'configuration' => 'array',
        'suspended_at' => 'datetime',

    ];


    // get user hosts
    public function getUserHosts($user_id = null)
    {
        return Cache::remember('user_hosts_' . $user_id ?? auth()->id(), 3600, function () use ($user_id) {
            return $this->where('user_id', $user_id)->with('module', function ($query) {
                $query->select(['id', 'name']);
            })->get();
        });
    }


    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // module
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // workOrders
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    // module 远程一对一
    // public function module() {
    //     return $this->hasOneThrough(Module::class, ProviderModule::class);
    // }


    // scope
    public function scopeActive($query)
    {
        return $query->where('status', 'running')->where('price', '!=', 0);
    }

    public function scopeThisUser($query, $module = null)
    {
        if ($module) {
            return $query->where('user_id', auth()->id())->where('module_id', $module);
        } else {
            return $query->where('user_id', auth()->id());
        }
    }


    // cost
    public function cost($price = null, $auto = true)
    {
        $this->load('user');

        $transaction = new Transaction();

        $drops = $transaction->getDrops($this->user_id);

        if ($price !== null) {
            $this->price = $price;
        }

        $amount = $price / config('drops.rate') + 1;

        // if drops <= price
        if ($drops < $this->price) {
            try {
                // 算出需要补充多少 Drops
                $need = $this->price - $drops;

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
            $hosts_drops[$this->id] += $this->price;
        } else {
            $hosts_drops[$this->id] = $this->price;
        }

        Cache::put($month_cache_key, $hosts_drops, 604800);

        $transaction->reduceDrops($this->user_id, $this->id, $this->module_id, $auto, $this->price);

        broadcast(new UserEvent($this->user_id, 'balances.drops.reduced', $this->user));

        // 检测用户余额是否足够
        if ($this->user->balance < 0) {
            $this->update([
                'status' => 'suspended',
            ]);
        }

        return true;
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            broadcast(new UserEvent($model->user_id, 'hosts.created', $model));
        });

        static::updating(function ($model) {
            if ($model->status == 'suspended') {
                $model->suspended_at = now();
            } else if ($model->status == 'running') {
                $model->suspended_at = null;
            }

            broadcast(new UserEvent($model->user_id, 'hosts.updating', $model));
        });

        // when Updated
        static::updated(function ($model) {
            dispatch(new \App\Jobs\Remote\Host($model, 'patch'));

            Cache::forget('user_hosts_' . $model->user_id);

            broadcast(new UserEvent($model->user_id, 'hosts.updated', $model));
        });

        //
        // static::deleting(function ($model) {
        //     broadcast(new UserEvent($model->user_id, 'hosts.deleting', $model));
        // });

        static::deleted(function ($model) {
            broadcast(new UserEvent($model->user_id, 'hosts.deleted', $model));

            Cache::forget('user_hosts_' . $model->user_id);
        });
    }
}
