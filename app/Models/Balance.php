<?php

namespace App\Models;

use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Support\Carbon;
use function auth;

/**
 * App\Models\Balance
 *
 * @property int            $id
 * @property string|null    $order_id
 * @property string|null    $trade_id
 * @property string|null    $payment
 * @property string         $amount
 * @property string         $remaining_amount
 * @property string|null    $paid_at
 * @property int|null       $user_id
 * @property Carbon|null    $created_at
 * @property Carbon|null    $updated_at
 * @property-read User|null $user
 * @method static CachedBuilder|Balance all($columns = [])
 * @method static CachedBuilder|Balance avg($column)
 * @method static CachedBuilder|Balance cache(array $tags = [])
 * @method static CachedBuilder|Balance cachedValue(array $arguments, string $cacheKey)
 * @method static CachedBuilder|Balance count($columns = '*')
 * @method static CachedBuilder|Balance disableCache()
 * @method static CachedBuilder|Balance disableModelCaching()
 * @method static CachedBuilder|Balance exists()
 * @method static CachedBuilder|Balance flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static CachedBuilder|Balance inRandomOrder($seed = '')
 * @method static CachedBuilder|Balance insert(array $values)
 * @method static CachedBuilder|Balance isCachable()
 * @method static CachedBuilder|Balance max($column)
 * @method static CachedBuilder|Balance min($column)
 * @method static CachedBuilder|Balance newModelQuery()
 * @method static CachedBuilder|Balance newQuery()
 * @method static CachedBuilder|Balance query()
 * @method static CachedBuilder|Balance sum($column)
 * @method static CachedBuilder|Balance thisUser()
 * @method static CachedBuilder|Balance truncate()
 * @method static CachedBuilder|Balance whereAmount($value)
 * @method static CachedBuilder|Balance whereCreatedAt($value)
 * @method static CachedBuilder|Balance whereId($value)
 * @method static CachedBuilder|Balance whereOrderId($value)
 * @method static CachedBuilder|Balance wherePaidAt($value)
 * @method static CachedBuilder|Balance wherePayment($value)
 * @method static CachedBuilder|Balance whereRemainingAmount($value)
 * @method static CachedBuilder|Balance whereTradeId($value)
 * @method static CachedBuilder|Balance whereUpdatedAt($value)
 * @method static CachedBuilder|Balance whereUserId($value)
 * @method static CachedBuilder|Balance withCacheCooldownSeconds(?int $seconds = null)
 * @mixin Eloquent
 */
class Balance extends Model
{
    use Cachable;

    protected $fillable = [
        'order_id',
        'payment',
        'amount',
        'user_id',
        'paid_at',
        'trade_id'
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->created_at->diffInDays(now()) > 1 && !$this->isPaid();
    }

    public function canPay(): bool
    {
        return !$this->isPaid() && !$this->isOverdue();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($balance) {
            // $balance->remaining_amount = $balance->amount;
            $balance->remaining_amount = 0;

            $balance->order_id = date('YmdHis') . $balance->id . rand(1000, 9999);
        });
    }
}
