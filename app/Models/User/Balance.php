<?php

namespace App\Models\User;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Balance extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'payment',
        'amount',
        'user_id',
        'paid_at',
        'trade_id'
    ];

    // route key
    public function getRouteKeyName()
    {
        return 'order_id';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
