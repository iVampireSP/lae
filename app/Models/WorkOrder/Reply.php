<?php

namespace App\Models\WorkOrder;

use App\Exceptions\CommonException;
use App\Models\Module;
use App\Models\User;
use App\Notifications\WorkOrder\Reply as ReplyNotification;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Reply extends Model
{
    use Cachable, Notifiable;

    protected $table = 'work_order_replies';

    protected $fillable = [
        'content',
        'work_order_id',
        'user_id',
        'name',
        'module_id',
        'is_pending',
        'role',
    ];

    protected $hidden = [
        'ip',
    ];

    protected static function booted()
    {
        static::creating(function (self $model) {
            $model->is_pending = false;

            // load work order
            $model->load(['workOrder']);

            // throw if work order is null
            if (is_null($model->workOrder)) {
                throw new CommonException('Work order not found');
            }

            throw_if($model->workOrder->isFailure(), CommonException::class, '工单还没有就绪。');

            // change work order status
            if (auth('admin')->check()) {
                $model->role = 'admin';
                $model->workOrder->status = 'replied';
            } elseif (auth('sanctum')->check()) {
                $model->user_id = auth('sanctum')->id();
                $model->role = 'user';
                $model->workOrder->status = 'user_replied';
            } elseif (auth('module')->check()) {
                $model->user_id = null;
                $model->role = 'module';
                $model->workOrder->status = 'replied';

            // broadcast(new Users($model->user_id, 'work-order.replied', $model->workOrder));
            } else {
                $model->role = 'guest';
            }

            $model->workOrder->save();

            if ($model->workOrder->isPlatform()) {
                $model->is_pending = false;
            }

            $model->ip = request()->ip();
        });

        static::created(function (self $model) {
            if (auth('module')->check()) {
                $model->workOrder->status = 'replied';
                $model->workOrder->save();
            }

            // notify
            $model->notify(new ReplyNotification($model));

            // dispatch
            dispatch(new \App\Jobs\WorkOrder\Reply($model, 'post'));
            dispatch(new \App\Jobs\WorkOrder\WorkOrder($model->workOrder, 'put'));
        });

        static::updating(function (self $model) {
            dispatch(new \App\Jobs\WorkOrder\Reply($model, 'patch'));
        });
    }

    public function scopeWorkOrderId($query, $work_order_id)
    {
        return $query->where('work_order_id', $work_order_id);
    }

    public function scopeWithUser($query)
    {
        return $query->with(['user' => function ($query) {
            $query->select('id', 'name', 'email_md5');
        }]);
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

    public function safeDelete(): void
    {
        dispatch(new \App\Jobs\WorkOrder\Reply($this, 'delete'));
    }

    public function routeNotificationForMail(): array
    {
        $user = $this->workOrder->user;

        return $user ? [$user->email => $user->name] : [];
    }
}
