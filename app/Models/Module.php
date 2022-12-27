<?php

namespace App\Models;

use Eloquent;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

/**
 * App\Models\Module
 *
 * @property string      $id
 * @property string      $name
 * @property string|null $api_token
 * @property string|null $url
 * @property string|null $wecom_key 企业微信机器人 key
 * @method static CachedBuilder|Module all($columns = [])
 * @method static CachedBuilder|Module avg($column)
 * @method static CachedBuilder|Module cache(array $tags = [])
 * @method static CachedBuilder|Module cachedValue(array $arguments, string $cacheKey)
 * @method static CachedBuilder|Module count($columns = '*')
 * @method static CachedBuilder|Module disableCache()
 * @method static CachedBuilder|Module disableModelCaching()
 * @method static CachedBuilder|Module exists()
 * @method static CachedBuilder|Module flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
 * @method static CachedBuilder|Module inRandomOrder($seed = '')
 * @method static CachedBuilder|Module insert(array $values)
 * @method static CachedBuilder|Module isCachable()
 * @method static CachedBuilder|Module max($column)
 * @method static CachedBuilder|Module min($column)
 * @method static CachedBuilder|Module newModelQuery()
 * @method static CachedBuilder|Module newQuery()
 * @method static CachedBuilder|Module query()
 * @method static CachedBuilder|Module sum($column)
 * @method static CachedBuilder|Module truncate()
 * @method static CachedBuilder|Module whereApiToken($value)
 * @method static CachedBuilder|Module whereId($value)
 * @method static CachedBuilder|Module whereName($value)
 * @method static CachedBuilder|Module whereUrl($value)
 * @method static CachedBuilder|Module whereWecomKey($value)
 * @method static CachedBuilder|Module withCacheCooldownSeconds(?int $seconds = null)
 * @mixin Eloquent
 */
class Module extends Authenticatable
{
    use Cachable;

    public $incrementing = false;

    // primary key
    public $timestamps = false;
    protected $table = 'modules';
    protected $keyType = 'string';
    protected $fillable = [
        'id',
        'name',
        'api_token'
    ];

    protected $hidden = [
        'api_token',
        'url',
        'wecom_key'
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            // if local
            if (!app()->environment('local')) {
                $model->api_token = Str::random(60);
            }
        });
    }

    // public function moduleHostFunctions($host_id, $func, $requests): array
    // {
    //     $http = Http::module($this->api_token, $this->url);
    //     $response = $http->post("hosts/{$host_id}/functions/" . $func, $requests);
    //
    //     return $this->getResponse($response);
    // }


    // post, get, patch, delete 等请求
    public function remote($func, $requests): array
    {
        $response = $this->http()->post('functions/' . $func, $requests);

        return $this->getResponse($response);
    }

    public function http(): PendingRequest
    {
        return Http::module($this->api_token, $this->url)->acceptJson()->timeout(5);
    }

    private function getResponse(Response $response): array
    {
        $json = $response->json();
        $status = $response->status();

        $success = true;

        // if status code is not 20x
        if ($status < 200 || $status >= 300) {
            $success = false;
        }

        return [
            'body' => $response->body(),
            'json' => $json,
            'status' => $status,
            'success' => $success,
        ];
    }

    public function request($method, $path, $requests): array
    {
        return $this->baseRequest($method, "functions/{$path}", $requests);
    }

    public function baseRequest($method, $path, $requests): array
    {
        $user = auth('sanctum')->user();

        if ($user) {
            $this->http()->withHeaders([
                'X-User-id' => $user->id,
            ]);
            $requests['user_id'] = $user->id;
            if ($method == 'post') {
                // add user to requests
                $requests['user'] = $user;
            }
        }

        $response = $this->http()->{$method}($path, $requests);

        return $this->getResponse($response);
    }

    public function moduleRequest($method, $path, $requests): array
    {
        $module_id = auth('module')->id();

        $http = $this->http()->withHeaders([
            'X-Module' => $module_id
        ]);

        $requests['module_id'] = $module_id;

        $response = $http->{$method}("exports/{$path}", $requests);

        return $this->getResponse($response);
    }

    public function check($module_id = null): bool
    {
        if ($module_id) {
            $module = Module::find($module_id);
        } else {
            $module = $this;
        }

        $success = 0;

        try {
            $response = $this->http()->get('remote');

            if ($response->status() == 200) {
                $success = 1;
            }
        } catch (ConnectException $e) {
            Log::error($e->getMessage());
        }

        return $success;
    }

    #[ArrayShape(['transactions' => "array"])]
    public function calculate(): array
    {
        $cache_key = 'module_earning_' . $this->id;
        return Cache::get($cache_key, []);
    }
}
