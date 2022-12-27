<?php

namespace App\Models;

use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * App\Models\PersonalAccessToken
 *
 * @property int                                               $id
 * @property string                                            $tokenable_type
 * @property int                                               $tokenable_id
 * @property string                                            $name
 * @property string                                            $token
 * @property array|null                                        $abilities
 * @property Carbon|null                   $last_used_at
 * @property Carbon|null                   $expires_at
 * @property Carbon|null                   $created_at
 * @property Carbon|null                   $updated_at
 * @property-read Model|Eloquent $tokenable
 * @method static CachedBuilder|PersonalAccessToken all($columns = [])
 * @method static CachedBuilder|PersonalAccessToken avg($column)
 * @method static CachedBuilder|PersonalAccessToken cache(array $tags = [])
 * @method static CachedBuilder|PersonalAccessToken cachedValue(array $arguments, string
 *         $cacheKey)
 * @method static CachedBuilder|PersonalAccessToken count($columns = '*')
 * @method static CachedBuilder|PersonalAccessToken disableCache()
 * @method static CachedBuilder|PersonalAccessToken disableModelCaching()
 * @method static CachedBuilder|PersonalAccessToken exists()
 * @method static CachedBuilder|PersonalAccessToken flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static CachedBuilder|PersonalAccessToken inRandomOrder($seed = '')
 * @method static CachedBuilder|PersonalAccessToken insert(array $values)
 * @method static CachedBuilder|PersonalAccessToken isCachable()
 * @method static CachedBuilder|PersonalAccessToken max($column)
 * @method static CachedBuilder|PersonalAccessToken min($column)
 * @method static CachedBuilder|PersonalAccessToken newModelQuery()
 * @method static CachedBuilder|PersonalAccessToken newQuery()
 * @method static CachedBuilder|PersonalAccessToken query()
 * @method static CachedBuilder|PersonalAccessToken sum($column)
 * @method static CachedBuilder|PersonalAccessToken truncate()
 * @method static CachedBuilder|PersonalAccessToken whereAbilities($value)
 * @method static CachedBuilder|PersonalAccessToken whereCreatedAt($value)
 * @method static CachedBuilder|PersonalAccessToken whereExpiresAt($value)
 * @method static CachedBuilder|PersonalAccessToken whereId($value)
 * @method static CachedBuilder|PersonalAccessToken whereLastUsedAt($value)
 * @method static CachedBuilder|PersonalAccessToken whereName($value)
 * @method static CachedBuilder|PersonalAccessToken whereToken($value)
 * @method static CachedBuilder|PersonalAccessToken whereTokenableId($value)
 * @method static CachedBuilder|PersonalAccessToken whereTokenableType($value)
 * @method static CachedBuilder|PersonalAccessToken whereUpdatedAt($value)
 * @method static CachedBuilder|PersonalAccessToken withCacheCooldownSeconds(?int
 *         $seconds = null)
 * @mixin Eloquent
 */
class PersonalAccessToken extends SanctumPersonalAccessToken
{
    use Cachable;

    /**
     * Limit saving of PersonalAccessToken records
     *
     * We only want to actually save when there is something other than
     * the last_used_at column that has changed. It prevents extra DB writes
     * since we aren't going to use that column for anything.
     *
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = [])
    {
        $changes = $this->getDirty();
        // Check for 2 changed values because one is always the updated_at column
        if (!array_key_exists('last_used_at', $changes) || count($changes) > 2) {
            parent::save();
        }
        return false;
    }
}
