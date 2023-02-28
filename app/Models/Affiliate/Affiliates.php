<?php

namespace App\Models\Affiliate;

use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Affiliates extends Model
{
    use Cachable;

    public $fillable = [
        'uuid',
        'visits',
        'revenue',
        'user_id',
    ];

    public $casts = [
        'visits' => 'integer',
        'revenue' => 'decimal:2',
    ];

    public static function booted()
    {
        static::creating(function (self $affiliate) {
            $affiliate->uuid = Str::ulid();
        });

        static::deleting(function (self $affiliate) {
            AffiliateUser::where('affiliate_id', $affiliate->id)->delete();
            $affiliate->user->update(['affiliate_id' => null]);
        });
    }

    public function scopeThisUser(Builder $query): Builder
    {
        return $query->where('user_id', auth('web')->id());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(AffiliateUser::class);
    }
}
