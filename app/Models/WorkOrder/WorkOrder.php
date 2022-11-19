<?php

namespace App\Models\WorkOrder;

use App\Exceptions\CommonException;
use App\Models\Host;
use App\Models\Module;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Jobs\Module\WorkOrder\WorkOrder as WorkOrderJob;

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

    public function safeDelete(): bool
    {
        if ($this->status == 'pending') {
            throw new CommonException('工单状态是 pending，无法删除');
        }

        dispatch(new WorkOrderJob($this, 'delete'));

        return true;
    }

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
            dispatch(new \App\Jobs\Module\WorkOrder\WorkOrder($model, 'put'));
        });
    }

    // replies

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // host

    public function replies(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function host(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    // scope

    public function module(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function scopeThisModule($query)
    {
        return $query->where('module_id', auth('module')->id());
    }


    // on create

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
