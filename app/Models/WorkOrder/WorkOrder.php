<?php

namespace App\Models\WorkOrder;

use App\Exceptions\CommonException;
use App\Jobs\Module\WorkOrder\WorkOrder as WorkOrderJob;
use App\Models\Host;
use App\Models\Module;
use App\Models\User;
use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * App\Models\WorkOrder\WorkOrder
 *
 * @property int                     $id
 * @property string                  $title
 * @property string                  $content
 * @property int                     $user_id
 * @property string                  $module_id
 * @property int|null                $host_id
 * @property string                  $status
 * @property Carbon|null             $created_at
 * @property Carbon|null             $updated_at
 * @property-read Host|null          $host
 * @property-read Module             $module
 * @property-read Collection|Reply[] $replies
 * @property-read int|null           $replies_count
 * @property-read User               $user
 * @method static CachedBuilder|WorkOrder all($columns = [])
 * @method static CachedBuilder|WorkOrder avg($column)
 * @method static CachedBuilder|WorkOrder cache(array $tags = [])
 * @method static CachedBuilder|WorkOrder cachedValue(array $arguments, string $cacheKey)
 * @method static CachedBuilder|WorkOrder count($columns = '*')
 * @method static CachedBuilder|WorkOrder disableCache()
 * @method static CachedBuilder|WorkOrder disableModelCaching()
 * @method static CachedBuilder|WorkOrder exists()
 * @method static CachedBuilder|WorkOrder flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static CachedBuilder|WorkOrder inRandomOrder($seed = '')
 * @method static CachedBuilder|WorkOrder insert(array $values)
 * @method static CachedBuilder|WorkOrder isCachable()
 * @method static CachedBuilder|WorkOrder max($column)
 * @method static CachedBuilder|WorkOrder min($column)
 * @method static CachedBuilder|WorkOrder newModelQuery()
 * @method static CachedBuilder|WorkOrder newQuery()
 * @method static CachedBuilder|WorkOrder query()
 * @method static CachedBuilder|WorkOrder sum($column)
 * @method static CachedBuilder|WorkOrder thisModule()
 * @method static CachedBuilder|WorkOrder thisUser()
 * @method static CachedBuilder|WorkOrder truncate()
 * @method static CachedBuilder|WorkOrder whereContent($value)
 * @method static CachedBuilder|WorkOrder whereCreatedAt($value)
 * @method static CachedBuilder|WorkOrder whereHostId($value)
 * @method static CachedBuilder|WorkOrder whereId($value)
 * @method static CachedBuilder|WorkOrder whereModuleId($value)
 * @method static CachedBuilder|WorkOrder whereStatus($value)
 * @method static CachedBuilder|WorkOrder whereTitle($value)
 * @method static CachedBuilder|WorkOrder whereUpdatedAt($value)
 * @method static CachedBuilder|WorkOrder whereUserId($value)
 * @method static CachedBuilder|WorkOrder withCacheCooldownSeconds(?int $seconds = null)
 * @mixin Eloquent
 */
class WorkOrder extends Model
{
    use Cachable;

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
            dispatch(new WorkOrderJob($model, 'put'));
        });
    }

    /**
     * @throws CommonException
     */
    public function safeDelete(): bool
    {
        if ($this->status == 'pending') {
            throw new CommonException('工单状态是 pending，无法删除');
        }

        dispatch(new WorkOrderJob($this, 'delete'));

        return true;
    }

    // replies

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // host

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }

    // scope

    public function module(): BelongsTo
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
