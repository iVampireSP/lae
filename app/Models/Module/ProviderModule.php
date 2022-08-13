<?php

namespace App\Models\Module;

use Illuminate\Support\Str;
use App\Models\Module\Module;
use App\Models\Module\Provider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProviderModule extends Model
{
    use HasFactory;

    protected $table = 'provider_modules';

    protected $fillable = [
        'provider_id',
        'module_id',
        'is_enabled',
        // 'api_token'
    ];

    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function getIsEnabledAttribute($value)
    {
        return (bool) $value;
    }

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // if local
            if (!app()->environment('local')) {
                $model->api_token = Str::random(60);
            }
        });
    }


}
