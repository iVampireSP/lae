<?php

namespace App\Models;

use App\Events\Users;
use App\Exceptions\CommonException;
use function auth;
use function broadcast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class Task extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'host_id',
        'title',
        'progress',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'progress' => 'integer',
    ];

    // key type string
    protected $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $model) {
            // id 为 uuid
            $model->id = Uuid::uuid4()->toString();

            // 如果是模块创建的任务
            if (auth('module')->check()) {
                $model->module_id = auth('module')->id();
            }

            // host_id 和 user_id 至少存在一个
            if (! $model->host_id && ! $model->user_id) {
                throw new CommonException('host_id 和 user_id 至少存在一个');
            }

            // if host_id
            if ($model->host_id) {
                $model->load('host');

                if ($model->host === null) {
                    throw new CommonException('host_id 不存在');
                }

                $model->user_id = $model->host->user_id;
            }
        });

        // created
        static::created(function (self $model) {
            $model->load('host');
            broadcast(new Users($model->user_id, 'tasks.created', $model));
        });

        // updating
        static::updating(function (self $model) {
            if ($model->progress == 100) {
                $model->status = 'done';
            }
        });

        // updated and delete
        static::updated(function (self $model) {
            $model->load('host');
            broadcast(new Users($model->user_id, 'tasks.updated', $model));
        });

        static::deleted(function (self $model) {
            broadcast(new Users($model->user_id, 'tasks.deleted', $model));
        });
    }

    // public function scopeUser($query)
    // {
    //     return $query->where('user_id', auth()->id());
    // }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }
}
