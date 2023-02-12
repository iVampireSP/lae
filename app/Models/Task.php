<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    public $incrementing = false;

    protected $fillable = [
        'host_id',
        'user_id',
        'title',
        'progress',
        'status',
    ];

    protected $casts = [
        'id' => 'string',
        'progress' => 'integer',
    ];

    // key type string
    protected $keyType = 'string';

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }
}
