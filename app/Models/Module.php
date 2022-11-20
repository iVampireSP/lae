<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Foundation\Auth\User as Authenticatable;
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

    public function remoteHost($host_id, $func, $requests)
    {
        $http = Http::module($this->api_token, $this->url);
        $response = $http->post("hosts/{$host_id}/functions/" . $func, $requests);

        $json = $response->json();
        $status = $response->status();

        return [$json, $status];
    }


    // post, get, patch, delete 等请求

    public function remote($func, $requests)
    {
        $http = Http::module($this->api_token, $this->url);
        $response = $http->post('functions/' . $func, $requests);

        $json = $response->json();
        $status = $response->status();

        return [$json, $status];
    }

    public function remoteRequest($method, $path, $requests)
    {
        $user = auth()->user();

        $http = Http::module($this->api_token, $this->url);

        // add Headers
        $http->withHeaders([
            'X-User' => $user->id
        ]);

        $requests['user_id'] = $user->id;


        if ($method == 'post') {
            // add user to requests
            $requests['user'] = $user;
        }

        $response = $http->{$method}("functions/{$path}", $requests);

        $json = $response->json();

        $status = $response->status();

        return [
            'body' => $response->body(),
            'json' => $json,
            'status' => $status
        ];
    }

    public function moduleRequest($method, $path, $requests)
    {
        $module_id = auth('module')->id();

        $http = Http::module($this->api_token, $this->url)
            ->accept('application/json');

        $http->withHeaders([
            'X-Module' => $module_id
        ]);

        $requests['module_id'] = $module_id;

        $response = $http->{$method}("exports/{$path}", $requests);

        $json = $response->json();

        $status = $response->status();
        return [
            'body' => $response->body(),
            'json' => $json,
            'status' => $status
        ];
    }

    public function remotePost($path = '', $data = [])
    {
        $http = Http::module($this->api_token, $this->url);
        $response = $http->post($path, $data);

        $json = $response->json();
        $status = $response->status();

        return [$json, $status];
    }


    // // get cached modules
    // public static function cached_modules()
    // {
    //     return Cache::remember('modules', 600, function () {
    //         return Module::all();
    //     });
    // }

    public function check($module_id = null)
    {
        if ($module_id) {
            $module = Module::find($module_id);
        } else {
            $module = $this;
        }

        try {
            $http = Http::module($module->api_token, $module->url);
            // dd($module->url);
            $response = $http->get('remote');
        } catch (ConnectException $e) {
            Log::error($e->getMessage());
        }

        if ($response->status() == 200) {
            return true;
        } else {
            return false;
        }
    }

    #[ArrayShape(['transactions' => "array"])]
    public function calculate(): array
    {
        $cache_key = 'module_earning_' . $this->id;

        return Cache::get($cache_key, []);
    }
}
