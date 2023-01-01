<?php

namespace App\Models\WorkOrder;

use App\Events\UserEvent;
use App\Exceptions\CommonException;
use App\Models\Module;
use App\Models\User;
use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * App\Models\WorkOrder\Reply
 *
 * @property int            $id
 * @property string         $content
 * @property int            $work_order_id
 * @property int|null       $user_id
 * @property int            $is_pending
 * @property Carbon|null    $created_at
 * @property Carbon|null    $updated_at
 * @property-read User|null $user
 * @property-read WorkOrder $workOrder
 * @method static CachedBuilder|Reply all($columns = [])
 * @method static CachedBuilder|Reply avg($column)
 * @method static CachedBuilder|Reply cache(array $tags = [])
 * @method static CachedBuilder|Reply cachedValue(array $arguments, string $cacheKey)
 * @method static CachedBuilder|Reply count($columns = '*')
 * @method static CachedBuilder|Reply disableCache()
 * @method static CachedBuilder|Reply disableModelCaching()
 * @method static CachedBuilder|Reply exists()
 * @method static CachedBuilder|Reply flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static CachedBuilder|Reply inRandomOrder($seed = '')
 * @method static CachedBuilder|Reply insert(array $values)
 * @method static CachedBuilder|Reply isCachable()
 * @method static CachedBuilder|Reply max($column)
 * @method static CachedBuilder|Reply min($column)
 * @method static CachedBuilder|Reply newModelQuery()
 * @method static CachedBuilder|Reply newQuery()
 * @method static CachedBuilder|Reply query()
 * @method static CachedBuilder|Reply sum($column)
 * @method static CachedBuilder|Reply truncate()
 * @method static CachedBuilder|Reply whereContent($value)
 * @method static CachedBuilder|Reply whereCreatedAt($value)
 * @method static CachedBuilder|Reply whereId($value)
 * @method static CachedBuilder|Reply whereIsPending($value)
 * @method static CachedBuilder|Reply whereUpdatedAt($value)
 * @method static CachedBuilder|Reply whereUserId($value)
 * @method static CachedBuilder|Reply whereWorkOrderId($value)
 * @method static CachedBuilder|Reply withCacheCooldownSeconds(?int $seconds = null)
 * @method static CachedBuilder|Reply workOrderId($work_order_id)
 * @mixin Eloquent
 */
class Reply extends Model
{
    use Cachable;

    protected $table = 'work_order_replies';

    protected $fillable = [
        'content',
        'work_order_id',
        'user_id',
        'name',
        'module_id',
        'is_pending',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $model) {
            $model->is_pending = 1;

            // load work order
            $model->load(['workOrder']);

            // throw if work order is null
            if (is_null($model->workOrder)) {
                throw new CommonException('Work order not found');
            }

            throw_if($model->workOrder->isFailure(), CommonException::class, '工单还没有就绪。');

            // change work order status
            if (auth('sanctum')->check()) {
                $model->user_id = auth()->id();
                $model->workOrder->status = 'user_replied';
            }

            if (auth('module')->check() || auth('admin')->check()) {
                $model->user_id = null;
                $model->workOrder->status = 'replied';

                broadcast(new UserEvent($model->user_id, 'work-order.replied', $model->workOrder));
            }

            $model->workOrder->save();
        });

        static::created(function ($model) {
            if (auth('module')->check()) {
                $model->workOrder->status = 'replied';
                $model->workOrder->save();
            }
            // dispatch
            dispatch(new \App\Jobs\Module\WorkOrder\Reply($model));
            dispatch(new \App\Jobs\Module\WorkOrder\WorkOrder($model->workOrder, 'put'));
        });
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id', 'id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    // before create
    public function scopeWorkOrderId($query, $work_order_id)
    {
        return $query->where('work_order_id', $work_order_id);
    }
}
