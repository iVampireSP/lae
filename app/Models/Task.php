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

/**
 * App\Models\Task
 *
 * @property string $id
 * @property string $title
 * @property int $progress
 * @property string $status
 * @property int $user_id
 * @property int $host_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Host $host
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task user()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereHostId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereProgress($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereStatus($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereTitle($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
class Task extends Model
{
    use HasFactory, Cachable;

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

    // before create

    public function host()
    {
        return $this->belongsTo(Host::class);
    }
}
