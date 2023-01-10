<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleAllow extends Model
{
    // use HasFactory;

    protected $fillable = [
        'module_id',
        'allowed_module_id',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function allowed_module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'allowed_module_id');
    }
}
