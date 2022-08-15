<?php

namespace App\Models\WorkOrder;

use App\Models\User\Host;
use Illuminate\Support\Arr;
use App\Models\Module\Module;
use App\Exceptions\CommonException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkOrder extends Model
{
    use HasFactory;

    protected $table = 'work_orders';

    protected $fillable = [
        'title',
        'content',
        'host_id',
        'user_id',
        'module_id',
        'status',
    ];


    // replies
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    // host
    public function host()
    {
        return $this->belongsTo(Host::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // scope
    public function scopeThisModule($query)
    {
        return $query->where('module_id', auth('remote')->id());
    }

    public function scopeUser($query)
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
            if (auth('sanctum')->check()) {
                $model->user_id = auth('sanctum')->id();

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

        // 更新时获取差异部分
        static::updating(function ($model) {
            $original = $model->getOriginal();
            // dd($original);
            $diff = array_diff_assoc($model->attributes, $original);

            // 如果更新了host_id，则抛出异常
            if (isset($diff['host_id'])) {
                throw new CommonException('host_id cannot be updated');
            }

            // queue patch diff
        });
    }
}
