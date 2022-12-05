<?php

namespace App\Models;

use App\Exceptions\ModuleRequestException;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
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
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module all($columns = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module avg($column)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module cache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module cachedValue(array $arguments, string $cacheKey)
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module count($columns = '*')
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module disableCache()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module disableModelCaching()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module exists()
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module flushCache(array $tags = [])
 * @method static \GeneaLabs\LaravelModelCaching\CachedBuilder|Module
 *         getModelCacheCooldown(\Illuminate\Database\Eloquent\Model $instance)
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

        $this->http()->withHeaders([
            'X-Module' => $module_id
        ]);

        $requests['module_id'] = $module_id;

        $response = $this->http()->{$method}("exports/{$path}", $requests);

        return $this->getResponse($response);
    }

    private function getResponse(Response $response): array
    {
        $json = $response->json();
        $status = $response->status();

        return [
            'body' => $response->body(),
            'json' => $json,
            'status' => $status
        ];
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

    /**
     * @throws ModuleRequestException
     */
    public function http(): PendingRequest
    {
        try {
            return Http::module($this->api_token, $this->url)->acceptJson()->timeout(5);
        } catch (ConnectException|RequestException $e) {
            throw new ModuleRequestException($e->getMessage());
        }
    }

    #[ArrayShape(['transactions' => "array"])]
    public function calculate(): array
    {
        $cache_key = 'module_earning_' . $this->id;
        return Cache::get($cache_key, []);
    }
}
