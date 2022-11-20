<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Admin
 *
 * @property int $id
 * @property string $email
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin query()
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Admin whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	class Admin extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Balance
 *
 * @property int $id
 * @property string|null $order_id
 * @property string|null $trade_id
 * @property string|null $payment
 * @property string $amount
 * @property string $remaining_amount
 * @property string|null $paid_at
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $user
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance thisUser()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereAmount($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereOrderId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance wherePaidAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance wherePayment($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereRemainingAmount($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereTradeId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Balance withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
	class Balance extends \Eloquent {}
}

namespace App\Models{

    use App\Models\WorkOrder\WorkOrder;

    /**
 * App\Models\Host
 *
 * @property int $id
 * @property string $name
 * @property string $module_id
 * @property int $user_id
 * @property float $price
 * @property float|null $managed_price
 * @property mixed|null $configuration
 * @property string $status
 * @property int|null $hour
 * @property \Illuminate\Support\Carbon|null $suspended_at
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Module $module
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection|WorkOrder[] $workOrders
 * @property-read int|null $work_orders_count
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host active()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host thisUser($module = null)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereConfiguration($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereDeletedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereHour($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereManagedPrice($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereModuleId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereName($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host wherePrice($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereStatus($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereSuspendedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Host withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
	class Host extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Module
 *
 * @property string $id
 * @property string $name
 * @property string|null $api_token
 * @property string|null $url
 * @property string|null $wecom_key 企业微信机器人 key
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module whereApiToken($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module whereName($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module whereUrl($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module whereWecomKey($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
	class Module extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\PersonalAccessToken
 *
 * @property int $id
 * @property string $tokenable_type
 * @property int $tokenable_id
 * @property string $name
 * @property string $token
 * @property array|null $abilities
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $expires_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $tokenable
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereAbilities($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereExpiresAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereLastUsedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereName($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereToken($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereTokenableId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereTokenableType($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
	class PersonalAccessToken extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Task
 *
 * @property string $id
 * @property string $title
 * @property int $progress
 * @property string $status
 * @property int $user_id
 * @property int $host_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Host $host
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task user()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereHostId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereProgress($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereStatus($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereTitle($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Task withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
	class Task extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property float $balance
 * @property \Illuminate\Support\Carbon|null $banned_at 封禁时间
 * @property string|null $banned_reason
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Host[] $hosts
 * @property-read int|null $hosts_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PersonalAccessToken[] $tokens
 * @property-read int|null $tokens_count
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User exists()
 * @method static \Database\Factories\UserFactory factory(...$parameters)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereBalance($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereBannedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereBannedReason($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereEmail($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereEmailVerifiedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereName($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User wherePassword($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereRememberToken($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|User withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
	class User extends \Eloquent {}
}

namespace App\Models\WorkOrder{

    use App\Models\User;

    /**
 * App\Models\WorkOrder\Reply
 *
 * @property int $id
 * @property string $content
 * @property int $work_order_id
 * @property int|null $user_id
 * @property int $is_pending
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read \App\Models\WorkOrder\WorkOrder $workOrder
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereContent($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereIsPending($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply whereWorkOrderId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply withCacheCooldownSeconds(?int $seconds = null)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Reply workOrderId($work_order_id)
 * @mixin \Eloquent
 */
	class Reply extends \Eloquent {}
}

namespace App\Models\WorkOrder{

    use App\Models\Host;
    use App\Models\Module;
    use Illuminate\Database\Eloquent\Collection;

    /**
 * App\Models\WorkOrder\WorkOrder
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property int $user_id
 * @property string $module_id
 * @property int|null $host_id
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Host|null $host
 * @property-read Module $module
 * @property-read Collection|\App\Models\WorkOrder\Reply[] $replies
 * @property-read int|null $replies_count
 * @property-read User $user
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder inRandomOrder($seed = '')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder insert(array $values)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder isCachable()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder max($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder min($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder newModelQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder newQuery()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder query()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder sum($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder thisModule()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder thisUser()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder truncate()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereContent($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereCreatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereHostId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereModuleId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereStatus($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereTitle($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereUpdatedAt($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder whereUserId($value)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|WorkOrder withCacheCooldownSeconds(?int $seconds = null)
 * @mixin \Eloquent
 */
	class WorkOrder extends \Eloquent {}
}

