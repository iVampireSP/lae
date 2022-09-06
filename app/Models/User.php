<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Sanctum\HasApiTokens;
use App\Exceptions\CommonException;
use App\Exceptions\User\BalanceNotEnoughException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance'
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'float',
    ];


    public function toDrops($amount = 1)
    {
        $rate = Cache::get('drops_rate', 100);
        $cache_key = 'user_drops_' . $this->id;

        $drops = Cache::get($cache_key, 0);

        $total = 0;

        if ($drops < 0) {
            $amount += abs($drops) / $rate;
        }

        $total += $amount * $rate;


        $lock = Cache::lock("lock_" . $cache_key, 5);
        try {
            $lock->block(5);

            $this->balance -= $amount;
            $this->save();

            // increment user drops
            Cache::increment($cache_key, $total);

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
