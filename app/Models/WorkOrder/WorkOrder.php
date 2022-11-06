<?php

namespace App\Models\WorkOrder;

use App\Exceptions\CommonException;
use App\Models\Host;
use App\Models\Module;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory, Cachable;

    protected $table = 'work_orders';

    protected $fillable = [
        'title',
        'content',
        'host_id',
        'user_id',
        'module_id',
        'status',
    ];

    // user
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // replies
    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reply::class);
    }

    // host
    public function host(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    public function module(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    // scope
    public function scopeThisModule($query)
    {
        return $query->where('module_id', auth('module')->id());
    }

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }


    // on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {

            if ($model->host_id) {
                $model->load(['host']);
                $model->module_id = $model->host->module_id;
            }

            // if logged
            if (auth()->check()) {
                $model->user_id = auth()->id();

                if ($model->host_id) {
                    if (!$model->user_id === $model->host->user_id) {
                        throw new CommonException('user_id not match host user_id');
                    }
                }
            } else {
                throw new CommonException('user_id is required');
            }


            if ($model->host_id) {
                $model->host->load('module');
                $module = $model->host->module;


                if ($module === null) {
                    $model->status = 'open';
                } else {
                    $model->status = 'pending';
                }
            }
        });

        // updated
        static::updated(function ($model) {
            dispatch(new \App\Jobs\Remote\WorkOrder\WorkOrder($model, 'put'));
        });
    }
}
