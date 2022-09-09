<?php

namespace App\Models;

use App\Models\Module\Module;
use App\Exceptions\CommonException;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Support\Facades\Cache;
// use Illuminate\Database\Eloquent\SoftDeletes;
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
    public function cost($price = null)
    {
        $this->load('user');

        $cache_key = 'user_drops_' . $this->user_id;

        $drops = Cache::get($cache_key);


        if ($price !== null) {
            $this->price = $price;
        }


        $amount = $price / Cache::get('drops_rate', 100) + 1;

        // if drops <= price
        if ($drops < $this->price) {
            try {
                // 算出需要补充多少 Drops
                $need = $this->price - $drops;

                // 算出需要补充多少余额
                $need_amount = $need / Cache::get('drops_rate', 100) + 1;

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

        $this->price = intval(log10(abs($this->price)) / 3);;

        Cache::decrement($cache_key, $this->price);

        return true;
    }

    /**
     * 创建主机
     *
     * 在此之后，所有的主机都将由 module 创建，并且主机的数据仅被用作计费。
     *
     * 废弃
     * @deprecated
     */
    // on create
    protected static function boot()
    {
        parent::boot();

        // static::creating(function ($model) {
        //     // if sanctum
        //     // if (auth('api')->check()) {
        //     //     $model->user_id = auth('api')->id();
        //     // } else {
        //     //     // if user_id is null
        //     //     // check user_id is exists
        //     //     throw_if(!User::find($model->user_id), CommonException::class, 'user is not exists');
        //     // }

        //     // // set price to 0
        //     // $model->price = 0;

        //     // $model->load('module');
        //     // $model->module->load(['provider', 'module']);

        //     // add to queue

        // });

        static::updating(function ($model) {
            if ($model->status == 'suspended') {
                $model->suspended_at = now();
            } else if ($model->status == 'running') {
                $model->suspended_at = null;
            }
        });

        // when Updated
        static::updated(function ($model) {
            dispatch(new \App\Jobs\Remote\Host($model, 'patch'));
        });

        // // when delete
        // static::deleting(function ($model) {
        //     // return false;

        //     // dispatch(new \App\Jobs\Remote\Host($model, 'delete'));
        // });
    }
}
