<?php

namespace App\Models;

use App\Models\Transaction;
use App\Exceptions\CommonException;
use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use App\Exceptions\User\BalanceNotEnoughException;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'balance'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'balance' => 'float',
        'banned_at' => 'datetime',
    ];


    public function toDrops($amount = 1)
    {
        $rate = config('drops.rate');

        $cache_key = 'user_drops_' . $this->id;

        $transactions = new Transaction();

        $drops = $transactions->getDrops($this->id);

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

    // when update
    // protected static function boot()
    // {
    //     parent::boot();

    //     // when update
    //     static::updating(function ($model) {

    //     });
    // }
}
