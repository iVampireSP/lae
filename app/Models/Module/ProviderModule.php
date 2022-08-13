<?php

namespace App\Models\Module;

use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderModule extends Model
{
    use HasFactory;

    protected $table = 'provider_modules';

    protected $fillable = [
        'provider_id',
        'module_id',
        'is_enabled',
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

    // before create
    // protected static function boot()
    // {
    //     parent::boot();
    //     static::creating(function ($model) {
            
    //     });
    // }


}
