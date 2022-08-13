<?php

namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    use HasFactory;

    protected $table = 'workorder_replies';

    protected $fillable = [
        'content',
        'work_order_id',
        'user_id',
        'is_pending',
    ];

}
