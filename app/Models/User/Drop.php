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

    protected $cache_key, $cache;

    protected $fillable = [
        'payment', 'amount', 'user_id', 'type'
    ];

    // casts
    protected $casts = [
        'amount' => 'double',
        'total' => 'double',
        'rate' => 'integer',
        'status' => 'boolean',
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

            $this->cache_key = 'user_' . $drops->user_id;

            // if cache has user

            // if (Cache::has($this->cache_key)) {
            //     // if user is not instances of Model
            //     $user = Cache::get($this->cache_key);
            //     if ($user instanceof User) {
            //         $this->await($this->cache_key, function () use ($user) {
            //             $user->save();
            //         });
            //     }

            //     // delete cache
            //     Cache::forget($this->cache_key);
            // }
        });

        // created
        self::created(function ($drops) {
            $drop = new self();
            $drop->await('user_' . $drops->user_id, function () use ($drops) {
                $cache = Cache::tags(['users']);
                $drops->load('user');
                $cache_key = 'user_' . $drops->user_id;

                // if cache has user
                if ($cache->has($cache_key)) {
                    $user = $cache->get($cache_key);
                    if (!($user instanceof User)) {
                        $user = $drops->user;
                    }

                    $user->drops += $drops->total;


                    $cache->put($cache_key, $user, 600);

                    $user->save();
                } else {
                    $drops->user->drops += $drops->total;
                    $drops->user->save();
                }
            });
        });
    }
}
