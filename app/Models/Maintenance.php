<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
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

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
