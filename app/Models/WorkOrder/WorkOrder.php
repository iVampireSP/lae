<?php

namespace App\Models\WorkOrder;

use App\Exceptions\CommonException;
use App\Jobs\Module\WorkOrder\WorkOrder as WorkOrderJob;
use App\Models\Host;
use App\Models\Module;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\WorkOrder\WorkOrder
 *
 * @property int                                                                         $id
 * @property string                                                                      $title
 * @property string                                                                      $content
 * @property int                                                                         $user_id
 * @property string                                                                      $module_id
 * @property int|null                                                                    $host_id
 * @property string                                                                      $status
 * @property \Illuminate\Support\Carbon|null                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                             $updated_at
 * @property-read Host|null                                                              $host
 * @property-read Module                                                                 $module
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\WorkOrder\Reply[] $replies
 * @property-read int|null                                                               $replies_count
 * @property-read User                                                                   $user
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder thisModule()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder thisUser()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereContent($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereHostId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereModuleId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereStatus($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereTitle($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
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

    public function safeDelete(): bool
    {
        if ($this->status == 'pending') {
            throw new CommonException('工单状态是 pending，无法删除');
        }

        dispatch(new WorkOrderJob($this, 'delete'));

        return true;
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
