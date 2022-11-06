<?php

namespace App\Models;

use App\Events\UserEvent;
use App\Exceptions\CommonException;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;
use function auth;
use function broadcast;

class Task extends Model
{
    use HasFactory, Cachable;

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

    public $incrementing = false;

    // key type string
    protected $keyType = 'string';

    public function scopeUser($query)
    {
        return $query->where('user_id', auth()->id());
    }


    public function getCurrentUserTasks()
    {
        return Cache::remember('user_tasks_' . auth()->id(), 3600, function () {
            return $this->user()->with('host')->latest()->get();
        });
    }


    public function host()
    {
        return $this->belongsTo(Host::class);
    }

    // before create
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // id 为 uuid
            $model->id = Uuid::uuid4()->toString();


            // host_id 和 user_id 至少存在一个
            if (!$model->host_id && !$model->user_id) {
                throw new CommonException('host_id 和 user_id 至少存在一个');
            }

            // if host_id
            if ($model->host_id) {
                $model->load('host');

                if ($model->host === null) {
                    throw new CommonException('host_id 不存在');
                }

                // dd($model);

                // dd($model->host_id);
                // $host = Host::where('id', $model->host_id)->first();

                // dd($host);



                $model->user_id = $model->host->user_id;

                Cache::forget('user_tasks_' . $model->user_id);
            }
        });

        // created
        static::created(function ($model) {
            $model->load('host');
            broadcast(new UserEvent($model->user_id, 'tasks.created', $model));
        });

        // updateing
        static::updating(function ($model) {
            if ($model->progress == 100) {
                $model->status = 'done';
            }
        });

        // updated and delete
        static::updated(function ($model) {
            // Cache::forget('user_tasks_' . $model->user_id);

            $model->load('host');
            broadcast(new UserEvent($model->user_id, 'tasks.updated', $model));
        });


        static::deleted(function ($model) {
            // Cache::forget('user_tasks_' . $model->user_id);

            broadcast(new UserEvent($model->user_id, 'tasks.deleted', $model));
        });
    }
}
