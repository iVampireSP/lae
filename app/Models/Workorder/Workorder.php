<?php

namespace App\Models\WorkOrder;

use App\Models\User\Host;
use App\Exceptions\CommonException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Arr;

class WorkOrder extends Model
{
    use HasFactory;

    protected $table = 'workorders';

    protected $fillable = [
        'title',
        'content',
        'host_id',
        'user_id',
        'provider_module_id',
        'status',
    ];


    // replies
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    // provider module
    public function host()
    {
        return $this->belongsTo(Host::class);
    }


    // on create
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->load('host');

            if (!$model->user_id === $model->host->user_id) {
                throw new CommonException('user_id not match host user_id');
            }

            $model->host->load('provider_module');
            $provider_module = $model->host->provider_module;


            if ($provider_module === null) {
                $model->status = 'open';
            } else {
                $model->status = 'pending';
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
