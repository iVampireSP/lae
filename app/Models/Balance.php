<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use function auth;

class Balance extends Model
{
    use HasFactory, Cachable;

    protected $fillable = [
        'order_id',
        'payment',
        'amount',
        'user_id',
        'paid_at',
        'trade_id'
    ];

    // route key
    public function getRouteKeyName(): string
    {
        return 'order_id';
    }

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }
}
