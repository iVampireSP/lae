<?php

namespace App\Models\User;

use App\Models\Host;
use App\Exceptions\CommonException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;

class Task extends Model
{
    use HasFactory;

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
            return $this->user()->with('host')->get();
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

                Cache::forget('user_tasks_' . auth()->id());
            }
        });

        // updateing
        static::updating(function ($model) {
            if ($model->progress == 100) {
                $model->status = 'done';
            }
        });

        // updated and delete
        static::updated(function () {
            Cache::forget('user_tasks_' . auth()->id());
        });


        static::deleted(function () {
            Cache::forget('user_tasks_' . auth()->id());
        });
    }
}
