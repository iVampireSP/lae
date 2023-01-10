<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo as BelongsToAlias;
use function auth;

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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($balance) {
            // $balance->remaining_amount = $balance->amount;
            $balance->remaining_amount = 0;

            $balance->order_id = date('YmdHis') . $balance->id . rand(1000, 9999);
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
        return !$this->isPaid() && !$this->isOverdue();
    }

    public function isPaid(): bool
    {
        return $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->created_at->diffInDays(now()) > 1 && !$this->isPaid();
    }
}
