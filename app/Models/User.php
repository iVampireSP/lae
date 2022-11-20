<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Exceptions\CommonException;
use App\Exceptions\User\BalanceNotEnoughException;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property float $balance
 * @property \Illuminate\Support\Carbon|null $banned_at 封禁时间
 * @property string|null $banned_reason
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Host[] $hosts
 * @property-read int|null $hosts_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User exists()
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereBalance($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereBannedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereBannedReason($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereEmail($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereEmailVerifiedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereName($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User wherePassword($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereRememberToken($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Cachable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'float',
        'banned_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {

            // balance 四舍五入

            if ($model->isDirty('balance')) {
                $model->balance = round($model->balance, 2);
            }

            if ($model->isDirty('banned_at')) {
                if ($model->banned_at) {
                    $model->tokens()->delete();
                    $model->hosts()->update(['status' => 'suspended', 'suspended_at' => now()]);
                } else {
                    $model->hosts()->update(['status' => 'stopped']);
                }
            }
        });
    }

    public function hosts(): HasMany
    {
        return $this->hasMany(Host::class);
    }

    /**
     * @throws CommonException
     * @throws BalanceNotEnoughException
     */
    public function toDrops($amount = 1)
    {

        $cache_key = 'user_drops_' . $this->id;

        if ($amount === 0 || $amount === null) {
            return $this;
        }

        $rate = config('drops.rate');


        $transactions = new Transaction();

        $drops = $transactions->getDrops($this->id);

        $total = 0;

        if ($drops < 0) {
            $amount += abs($drops) / $rate;
        }

        $total += $amount * $rate;


        // amount 保留两位小数
        $amount = round($amount, 2);

        $lock = Cache::lock("lock_" . $cache_key, 5);
        try {
            $lock->block(5);

            $this->balance -= $amount;
            $this->save();

            $transactions->increaseDrops($this->id, $total);

            // $transactions

            $transactions->addPayoutBalance($this->id, $amount, '自动转换为 Drops');

            // if user balance <= 0
            if ($this->balance < $amount) {
                throw new BalanceNotEnoughException('余额不足');
            }
        } catch (LockTimeoutException) {
            throw new CommonException('暂时无法处理此请求，请稍后再试。');
        } finally {
            optional($lock)->release();
        }

        return $this;
    }
}
