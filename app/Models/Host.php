<?php

namespace App\Models;

use App\Exceptions\CommonException;
use App\Models\Module\Module;
use App\Models\WorkOrder\WorkOrder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Host extends Model
{
    use HasFactory, SoftDeletes;

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
        'configuration' => 'array'
    ];


    // user
    public function user() {
        return $this->belongsTo(User::class);
    }

    // module
    public function module() {
        return $this->belongsTo(Module::class);
    }

    // workOrders
    public function workOrders() {
        return $this->hasMany(WorkOrder::class);
    }

    // module 远程一对一
    // public function module() {
    //     return $this->hasOneThrough(Module::class, ProviderModule::class);
    // }


    // scope
    public function scopeActive($query) {
        return $query->where('status', 'running')->where('price', '!=', 0);
    }

    // on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // if sanctum
            if (auth('sanctum')->check()) {
                $model->user_id = auth('sanctum')->id();
            } else {
                // if user_id is null
                // check user_id is exists
                throw_if(!User::find($model->user_id), CommonException::class, 'user is not exists');
            }

            // $model->load('module');
            // $model->module->load(['provider', 'module']);

            // add to queue

        });
    }
}
