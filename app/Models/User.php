<?php

namespace App\Models;

use App\Events\Users;
use App\Exceptions\User\BalanceNotEnoughException;
use App\Models\Affiliate\Affiliates;
use App\Models\Affiliate\AffiliateUser;
use App\Notifications\User\BalanceNotEnough;
use App\Notifications\User\LowBalance;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Prunable, Cachable;

    public array $publics = [
        'id',
        'uuid',
        'name',
        'email',
        'real_name',
        'balance',
        'user_group_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'receive_marketing_email',
        'affiliate_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'real_name',
        'id_card',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'real_name_verified_at' => 'datetime',
        'balance' => 'decimal:4',
        'banned_at' => 'datetime',
        'birthday_at' => 'date:Y-m-d',
    ];

    protected $dates = [
        'email_verified_at',
        'real_name_verified_at',
        'banned_at',
        'birthday_at',
    ];

    public function hosts(): HasMany
    {
        return $this->hasMany(Host::class);
    }

    public function affiliate(): HasOne
    {
        return $this->hasOne(Affiliates::class);
    }

    public function affiliateUsers(): HasManyThrough
    {
        return $this->hasManyThrough(AffiliateUser::class, Affiliates::class, 'user_id', 'affiliate_id');
    }

    public function affiliateUser(): BelongsTo
    {
        return $this->belongsTo(AffiliateUser::class, 'affiliate_id');
    }

    // 通过 affiliate_id 获取到 affiliates 中的 user_id
    public function promoter(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, Affiliates::class, 'id', 'id', 'affiliate_id', 'user_id');
    }

    public function getBirthdayFromIdCard(string|null $id_card = null): Carbon
    {
        if (empty($id_card)) {
            $id_card = $this->id_card;
        }

        $bir = substr($id_card, 6, 8);
        $year = (int) substr($bir, 0, 4);
        $month = (int) substr($bir, 4, 2);
        $day = (int) substr($bir, 6, 2);

        return Carbon::parse($year.'-'.$month.'-'.$day);
    }

    public function hasBalance(string $amount = '0.01'): bool
    {
        return bccomp($this->balance, $amount, 4) >= 0;
    }

    public function isAdult(): bool
    {
        // 如果 birthday_at 为空，那么就返回 false
        return $this->birthday_at?->diffInYears(now()) >= 18;
    }

    public function isRealNamed(): bool
    {
        return $this->real_name_verified_at !== null;
    }

    public function user_group(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }

    public function scopeBirthday(): Builder|CachedBuilder
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->select(['id', 'name', 'birthday_at', 'email_md5', 'created_at'])->whereMonth('birthday_at', now()->month)
            ->whereDay('birthday_at', now()->day)->whereNull('banned_at');
    }

    public function selectPublic(): self|Builder|CachedBuilder
    {
        // 仅需选择公开的
        return $this->select($this->publics);
    }
    
    public function getOnlyPublic($appened_excepts = [], $display = []): array
    {
        if ($display) {
            $this->publics = array_merge($this->publics, $display);
        }
        if ($appened_excepts) {
            $this->publics = array_diff($this->publics, $appened_excepts);
        }

        return Arr::only($this->toArray(), $this->publics);
    }

    public function prunable(): self|Builder|CachedBuilder
    {
        return static::where('deleted_at', '<=', now()->subWeek());
    }

    public function startTransfer(self $to, string $amount, string|null $description)
    {
        $description_from = "转账给 $to->name($to->email)";
        $description_to = "收到 $this->name($this->email) 的转账";

        if ($description) {
            $description_from .= "，备注：$description";
            $description_to .= "，备注：$description";
        }

        $this->reduce($amount, $description_from, true);

        $to->charge($amount, 'transfer', $description_to);

        return $this->balance;
    }

    /**
     * 扣除费用
     */
    public function reduce(string|null $amount = '0', string $description = '消费', bool $fail = false, array $options = []): Transaction
    {
        if ($amount === null || $amount === '') {
            return $this->balance;
        }

        /**
         * @throws BalanceNotEnoughException
         */
        return Cache::lock('user_balance_'.$this->id, 10)->block(10, function () use ($amount, $fail, $description, $options) {
            $this->refresh();

            if ($this->balance < $amount) {
                if ($fail) {
                    // 发送邮件通知
                    $this->notify(new BalanceNotEnough());

                    throw new BalanceNotEnoughException();
                }
            }

            $this->balance = bcsub($this->balance, $amount, 4);
            $this->save();

            $data = [
                'user_id' => $this->id,
                'amount' => $amount,
                'description' => $description,
                'payment' => 'balance',
                'type' => 'payout',
            ];

            if ($options) {
                $data = array_merge($data, $options);
            }

            broadcast(new Users($this, 'balances.amount.reduced', $this));

            // 如果用户的余额小于 5 元，则发送邮件提醒（一天只发送一次，使用缓存）
            if (! $this->hasBalance(5) && ! Cache::has('user_balance_less_than_5_'.$this->id)) {
                $this->notify(new LowBalance());
                Cache::put('user_balance_less_than_5_'.$this->id, true, now()->addDay());
            }

            return (new Transaction)->create($data);
        });
    }

    /**
     * 增加余额
     */
    public function charge(string|null $amount = '0', string $payment = 'console', string $description = '充值', array $options = []): Transaction
    {
        if ($amount === null || $amount === '') {
            return $this->balance;
        }

        return Cache::lock('user_balance_'.$this->id, 10)->block(10, function () use ($amount, $description, $payment, $options) {
            $this->refresh();
            $this->balance = bcadd($this->balance, $amount, 4);
            $this->save();

            $data = [
                'user_id' => $this->id,
                'amount' => $amount,
                'payment' => $payment,
                'description' => $description,
                'type' => 'income',
            ];

            if ($options) {
                $data = array_merge($data, $options);
            }

            if (isset($options['add_balances_log']) && $options['add_balances_log'] === true) {
                (new Balance)->create([
                    'user_id' => $this->id,
                    'amount' => $amount,
                    'payment' => $payment,
                    'description' => $description,
                    'paid_at' => now(),
                ]);
            }

            return (new Transaction)->create($data);
        });
    }

    public function getCostPrice(string $price): string
    {
        $this->load('user_group');

        if (! $this->user_group) {
            return $price;
        }

        return $this->user_group->getCostPrice($price);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * 获取用户的身份证号
     */
    protected function idCard(): Attribute
    {
        return Attribute::make(
            function ($value) {
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException) {
                    return $value;
                }
            }
        );
    }
}
