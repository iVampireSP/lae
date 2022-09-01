<?php

namespace App\Models;

use Log;
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

        $price = abs($price);


        if ($this->user->balance < 10) {
            $amount = 1;
        } else if ($this->user->balance < 100) {
            $amount = 10;
        } else if ($this->user->balance < 1000) {
            $amount = 100;
        } else if ($this->user->balance < 10000) {
            $amount = 1000;
        } else {
            $amount = 10000;
        }

        $cache_key = 'user_drops_' . $this->user_id;

        $drops = Cache::get($cache_key);



        // Log::debug($user);

        if ($price !== null) {
            $this->managed_price = $price;
        }

        if ($this->managed_price) {
            $this->price = $this->managed_price;
        }

        // if drops <= price
        if ($drops < $this->price) {
            try {
                $this->user->toDrops($amount);
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
        //     // if (auth('sanctum')->check()) {
        //     //     $model->user_id = auth('sanctum')->id();
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
