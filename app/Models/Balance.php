<?php

namespace App\Models;

use App\Events\Users;
use App\Notifications\User\UserCharged;
use function auth;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use Illuminate\Notifications\Notifiable;

class Balance extends Model
{
    use Cachable, Notifiable;

    protected $fillable = [
        'order_id',
        'payment',
        'amount',
        'user_id',
        'paid_at',
        'trade_id',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $balance) {
            // $balance->remaining_amount = $balance->amount;
            $balance->remaining_amount = 0;

            $balance->order_id = date('YmdHis').$balance->id.rand(1000, 9999);
        });

        static::created(function (self $balance) {
            broadcast(new Users($balance->user, 'balance.created', $balance));
        });

        static::updated(function (self $balance) {
            if ($balance->isDirty('paid_at')) {
                if ($balance->paid_at) {
                    $balance->notify(new UserCharged());
                    broadcast(new Users($balance->user, 'balance.updated', $balance));

                    $balance->user->charge($balance->amount, $balance->payment, $balance->order_id);
                }
            }
        });
    }

    public function user(): BelongsToAlias
    {
        return $this->belongsTo(User::class);
    }

    public function scopeThisUser($query)
    {
        return $query->where('user_id', auth()->id());
    }

    public function canPay(): bool
    {
        return ! $this->isPaid() && ! $this->isOverdue();
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->created_at->diffInDays(now()) > 1 && ! $this->isPaid();
    }
}
