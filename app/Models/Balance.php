<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use function auth;

/**
 * App\Models\Balance
 *
 * @property int $id
 * @property string|null $order_id
 * @property string|null $trade_id
 * @property string|null $payment
 * @property string $amount
 * @property string $remaining_amount
 * @property string|null $paid_at
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance thisUser()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereAmount($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereOrderId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance wherePaidAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance wherePayment($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereRemainingAmount($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereTradeId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
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
