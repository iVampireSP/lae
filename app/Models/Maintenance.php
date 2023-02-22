<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Maintenance extends Model
{
    use Cachable;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'content',
        'module_id',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected $dates = [
        'start_at',
        'end_at',
    ];

    protected $with = [
        'module',
    ];

    // 根据 start_at 排序
    public function scopeOrderByStartAt($query)
    {
        return $query->orderBy('start_at', 'desc');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
