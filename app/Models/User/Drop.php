<?php

namespace App\Models\User;

use App\Models\User;
use App\Helpers\Lock;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Drop extends Model
{
    use HasFactory, Lock;

    protected $fillable = [
        'payment', 'amount', 'user_id', 'type'
    ];

    // user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // before create
    public static function boot()
    {
        parent::boot();
        self::creating(function ($drops) {
            // if not admin auth guard
            if (!auth()->guard('admin')->check()) {
                $drops->user_id = auth()->id();
            }


            $rate = Cache::get('drops_rate', 100);
            $drops->total = $drops->amount * $rate;
        });

        // created
        self::created(function ($drops) {
            $drop = new self();
            $drop->await('user_drops_' . $drops->user_id, function () use ($drops) {
                $drops->user->drops += $drops->total;
                $drops->user->save();
            });
        });
    }
}
