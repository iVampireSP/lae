<?php

namespace App\Models\WorkOrder;

use App\Events\UserEvent;
use App\Exceptions\CommonException;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\WorkOrder\Reply
 *
 * @property int                                  $id
 * @property string                               $content
 * @property int                                  $work_order_id
 * @property int|null                             $user_id
 * @property int                                  $is_pending
 * @property \Illuminate\Support\Carbon|null      $created_at
 * @property \Illuminate\Support\Carbon|null      $updated_at
 * @property-read User|null                       $user
 * @property-read \App\Models\WorkOrder\WorkOrder $workOrder
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereContent($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereIsPending($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereWorkOrderId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply withCacheCooldownSeconds(?int $seconds = null)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply workOrderId($work_order_id)
 * @mixin \Eloquent
 */
class Reply extends Model
{
    use Cachable;

    protected $table = 'work_order_replies';

    protected $fillable = [
        'content',
        'work_order_id',
        'user_id',
        'is_pending',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            $model->is_pending = 1;

            // load work order
            $model->load(['workOrder']);

            throw_if($model->workOrder->status == 'pending' || $model->workOrder->status == 'error', CommonException::class, '工单状态不正确');

            // change work order status
            if (auth()->check()) {
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
