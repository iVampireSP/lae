<?php

namespace App\Models\Affiliate;

use App\Models\User;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Facades\Cache;

class AffiliateUser extends Model
{
    use Cachable;

    public $fillable = [
        'revenue',
        'affiliate_id',
        'user_id',
    ];

    public $casts = [
        'revenue' => 'decimal:2',
    ];

    public $with = [
        'user',
        'originalAffiliateUser',
    ];

    public function affiliate(): BelongsTo
    {
        return $this->belongsTo(Affiliates::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function originalAffiliateUser(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            Affiliates::class,
            'user_id',
            'id',
            'affiliate_id',
            'user_id'
        );
    }

    // 给用户添加佣金
    public function addRevenue(string $revenue): void
    {
        $this->load('user');

        // if (! $this->user->isRealNamed()) {
        //     return;
        // }

        $this->load('affiliate.user');

        // 给 affiliate_id 中的 user_id 添加佣金
        Cache::lock('affiliate_user_'.$this->id, 10)->block(10, function () use ($revenue) {
            // 计算应得
            $commission_referral = config('settings.billing.commission_referral') * 100;

            $revenue = bcdiv($revenue, $commission_referral, 2);

            $this->update([
                'revenue' => bcadd($this->revenue, $revenue, 2),
            ]);

            // 给上级添加佣金
            if ($this->affiliate->user_id) {
                $this->affiliate->update([
                    'revenue' => bcadd($this->affiliate->revenue, $revenue, 2),
                ]);

                $this->affiliate->user->charge($revenue, 'affiliate', '下属用户 '.$this->user->name.'#'.$this->user_id.' 充值所获得的佣金。');
            }
        });
    }
}
