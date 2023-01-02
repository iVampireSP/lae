<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * App\Models\User
 *
 * @property int
 *               $id
 * @property string
 *               $name
 * @property string
 *               $email
 * @property Carbon|null
 *               $email_verified_at
 * @property string|null
 *               $password
 * @property float
 *               $balance
 * @property Carbon|null
 *               $banned_at 封禁时间
 * @property string|null
 *               $banned_reason
 * @property string|null
 *               $remember_token
 * @property Carbon|null
 *               $created_at
 * @property Carbon|null
 *               $updated_at
 * @property-read Collection|Host[]
 *                    $hosts
 * @property-read int|null
 *                    $hosts_count
 * @property-read DatabaseNotificationCollection|DatabaseNotification[]
 *                $notifications
 * @property-read int|null
 *                    $notifications_count
 * @property-read Collection|PersonalAccessToken[]
 *                    $tokens
 * @property-read int|null
 *                    $tokens_count
 * @method static CachedBuilder|User all($columns = [])
 * @method static CachedBuilder|User avg($column)
 * @method static CachedBuilder|User cache(array $tags = [])
 * @method static CachedBuilder|User cachedValue(array $arguments, string $cacheKey)
 * @method static CachedBuilder|User count($columns = '*')
 * @method static CachedBuilder|User disableCache()
 * @method static CachedBuilder|User disableModelCaching()
 * @method static CachedBuilder|User exists()
 * @method static UserFactory factory(...$parameters)
 * @method static CachedBuilder|User flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static CachedBuilder|User inRandomOrder($seed = '')
 * @method static CachedBuilder|User insert(array $values)
 * @method static CachedBuilder|User isCachable()
 * @method static CachedBuilder|User max($column)
 * @method static CachedBuilder|User min($column)
 * @method static CachedBuilder|User newModelQuery()
 * @method static CachedBuilder|User newQuery()
 * @method static CachedBuilder|User query()
 * @method static CachedBuilder|User sum($column)
 * @method static CachedBuilder|User truncate()
 * @method static CachedBuilder|User whereBalance($value)
 * @method static CachedBuilder|User whereBannedAt($value)
 * @method static CachedBuilder|User whereBannedReason($value)
 * @method static CachedBuilder|User whereCreatedAt($value)
 * @method static CachedBuilder|User whereEmail($value)
 * @method static CachedBuilder|User whereEmailVerifiedAt($value)
 * @method static CachedBuilder|User whereId($value)
 * @method static CachedBuilder|User whereName($value)
 * @method static CachedBuilder|User wherePassword($value)
 * @method static CachedBuilder|User whereRememberToken($value)
 * @method static CachedBuilder|User whereUpdatedAt($value)
 * @method static CachedBuilder|User withCacheCooldownSeconds(?int $seconds = null)
 * @mixin Eloquent
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
        'balance' => 'decimal:2',
        'banned_at' => 'datetime',
        'birthday_at' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($model) {

            // balance 四舍五入

            // if ($model->isDirty('balance')) {
            //     $model->balance = round($model->balance, 2, PHP_ROUND_HALF_DOWN);
            // }

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

    public function user_group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function scopeBirthday()
    {
        return $this->select(['id', 'name', 'birthday_at', 'email_md5', 'created_at'])->whereMonth('birthday_at', now()->month)
            ->whereDay('birthday_at', now()->day);
    }
}
