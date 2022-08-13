<?php

namespace App\Models\WorkOrder;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $table = 'workorders';

    protected $fillable = [
        'title',
        'content',
        'user_id',
        'provider_module_id',
        'status',
    ];


    // replies
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }
}
