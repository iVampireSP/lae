<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

/**
 * App\Models\PersonalAccessToken
 *
 * @property int                                                $id
 * @property string                                             $tokenable_type
 * @property int                                                $tokenable_id
 * @property string                                             $name
 * @property string                                             $token
 * @property array|null                                         $abilities
 * @property \Illuminate\Support\Carbon|null                    $last_used_at
 * @property \Illuminate\Support\Carbon|null                    $expires_at
 * @property \Illuminate\Support\Carbon|null                    $created_at
 * @property \Illuminate\Support\Carbon|null                    $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $tokenable
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken cachedValue(array $arguments, string
 *         $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
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
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|PersonalAccessToken withCacheCooldownSeconds(?int
 *         $seconds = null)
 * @mixin \Eloquent
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
