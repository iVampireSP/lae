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

    public function hosts(): HasMany
    {
        return $this->hasMany(Host::class);
    }

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
