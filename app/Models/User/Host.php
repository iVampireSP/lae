<?php

namespace App\Models\User;

use App\Models\User;
use App\Models\Module\Module;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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

    // workorders
    public function workorders() {
        return $this->hasMany(Workorder::class);
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
            // $model->load('module');
            // $model->module->load(['provider', 'module']);

            // add to queue

        });
    }
}
