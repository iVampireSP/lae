<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        'uuid',
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

        static::creating(function (self $user) {
            $user->email_md5 = md5($user->email);
        });

        static::updating(function ($model) {
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
        /** @noinspection PhpUndefinedMethodInspection */
        return $this->select(['id', 'name', 'birthday_at', 'email_md5', 'created_at'])->whereMonth('birthday_at', now()->month)
            ->whereDay('birthday_at', now()->day)->whereNull('banned_at');
    }

    public function selectPublic(): User
    {
        // 过滤掉私有字段
        return $this->select(['id', 'name', 'email_md5', 'created_at']);
    }
}
