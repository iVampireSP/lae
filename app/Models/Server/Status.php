<?php

namespace App\Models\Server;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;

    protected $table = 'server_status';

    protected $fillable = [
        'name',
        'ip',
        'status',
        'module_id',
    ];

    // scope
    public function scopeModule($query)
    {
        return $query->where('module_id', auth('remote')->id());
    }


    // when update, check owner
    protected static function boot()
    {
        parent::boot();
        static::updating(function ($model) {
            if ($model->module_id !== auth('remote')->id()) {
                abort(403, 'Unauthorized action.');
            }
        });
    }
}
