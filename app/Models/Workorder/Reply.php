<?php

namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $table = 'work_order_replies';

    protected $fillable = [
        'content',
        'work_order_id',
        // 'user_id',
        'is_pending',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
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

            // load work order
            $model->load(['workOrder']);
            // change work order status
            if (auth('sanctum')->check()) {
                $model->user_id = auth()->id();
                $model->workOrder->status = 'user_replied';

            }

            if (auth('remote')->check()) {
                $model->user_id = null;
                $model->workOrder->status = 'replied';
            }

            $model->workOrder->save();

        });
    }

}
