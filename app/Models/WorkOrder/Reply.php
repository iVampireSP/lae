<?php

namespace App\Models\WorkOrder;

use App\Events\UserEvent;
use App\Exceptions\CommonException;
use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory, Cachable;

    protected $table = 'work_order_replies';

    protected $fillable = [
        'content',
        'work_order_id',
        'user_id',
        'is_pending',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'work_order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeWorkOrderId($query, $work_order_id)
    {
        return $query->where('work_order_id', $work_order_id);
    }


    // before create
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

            if (auth('remote')->check()) {
                $model->user_id = null;
                $model->workOrder->status = 'replied';

                broadcast(new UserEvent($model->user_id, 'work-order.replied', $model->workOrder));
            }

            $model->workOrder->save();
        });

        static::created(function ($model) {
            if (auth('remote')->check()) {
                $model->workOrder->status = 'replied';
                $model->workOrder->save();
            }
            // dispatch
            dispatch(new \App\Jobs\Remote\WorkOrder\Reply($model));
            dispatch(new \App\Jobs\Remote\WorkOrder\WorkOrder($model->workOrder, 'put'));
        });
    }
}
