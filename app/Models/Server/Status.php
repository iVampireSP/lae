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
        'provider_id',
    ];

    // scope
    public function scopeProvider($query)
    {
        return $query->where('provider_id', auth('remote')->id());
    }
}
