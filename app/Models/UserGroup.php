<?php

namespace App\Models;

use Carbon\Carbon;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class UserGroup extends Model
{
    use Cachable;

    public $fillable = [
        'name',
        'color',
        'discount',
        'exempt',
    ];

    public $casts = [
        'discount' => 'integer',
        'exempt' => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * 设置临时用户组
     *
     * @param  User  $user
     * @param  UserGroup  $group
     * @param  Carbon  $expired_at
     * @return User
     */
    public function setTempGroup(User $user, self $group, Carbon $expired_at): User
    {
        $temp_groups = Cache::get('users_temp_groups', []);

        // 检测是否存在，存在则更新（更新过期时间，但是不更新 user_group_id）
        if (isset($temp_groups[$user->id])) {
            $temp_groups[$user->id]['expired_at'] = $expired_at;
        } else {
            $temp_groups[$user->id] = [
                'user_group_id' => $user->user_group_id,
                'expired_at' => $expired_at,
            ];
        }

        // 保存到缓存
        Cache::forever('users_temp_groups', $temp_groups);

        // 设置新的用户组
        $user->user_group_id = $group->id;
        $user->save();

        return $user;
    }
}
