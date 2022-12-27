<?php

namespace App\Models;

use App\Events\UserEvent;
use App\Exceptions\CommonException;
use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Ramsey\Uuid\Uuid;
use function auth;
use function broadcast;

/**
 * App\Models\Task
 *
 * @property string                          $id
 * @property string                          $title
 * @property int                             $progress
 * @property string                          $status
 * @property int                             $user_id
 * @property int                             $host_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Host                       $host
 * @method static CachedBuilder|Task all($columns = [])
 * @method static CachedBuilder|Task avg($column)
 * @method static CachedBuilder|Task cache(array $tags = [])
 * @method static CachedBuilder|Task cachedValue(array $arguments, string $cacheKey)
 * @method static CachedBuilder|Task count($columns = '*')
 * @method static CachedBuilder|Task disableCache()
 * @method static CachedBuilder|Task disableModelCaching()
 * @method static CachedBuilder|Task exists()
 * @method static CachedBuilder|Task flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static CachedBuilder|Task inRandomOrder($seed = '')
 * @method static CachedBuilder|Task insert(array $values)
 * @method static CachedBuilder|Task isCachable()
 * @method static CachedBuilder|Task max($column)
 * @method static CachedBuilder|Task min($column)
 * @method static CachedBuilder|Task newModelQuery()
 * @method static CachedBuilder|Task newQuery()
 * @method static CachedBuilder|Task query()
 * @method static CachedBuilder|Task sum($column)
 * @method static CachedBuilder|Task truncate()
 * @method static CachedBuilder|Task user()
 * @method static CachedBuilder|Task whereCreatedAt($value)
 * @method static CachedBuilder|Task whereHostId($value)
 * @method static CachedBuilder|Task whereId($value)
 * @method static CachedBuilder|Task whereProgress($value)
 * @method static CachedBuilder|Task whereStatus($value)
 * @method static CachedBuilder|Task whereTitle($value)
 * @method static CachedBuilder|Task whereUpdatedAt($value)
 * @method static CachedBuilder|Task whereUserId($value)
 * @method static CachedBuilder|Task withCacheCooldownSeconds(?int $seconds = null)
 * @mixin Eloquent
 */
class Task extends Model
{
    use HasFactory;

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

            // 如果是模块创建的任务
            if (auth('module')->check()) {
                $model->module_id = auth('module')->id();
            }

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
            broadcast(new UserEvent($model->user_id, 'tasks.deleted', $model));
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
