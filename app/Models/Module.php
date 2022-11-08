<?php

namespace App\Models;

use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Module extends Authenticatable
{
    use Cachable;

    protected $table = 'modules';

    // primary key
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

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


    public function remoteHost($host_id, $func, $requests)
    {
        $http = Http::remote($this->api_token, $this->url);
        $response = $http->post("hosts/{$host_id}/functions/" . $func, $requests);

        $json = $response->json();
        $status = $response->status();

        return [$json, $status];
    }

    public function remote($func, $requests)
    {
        $http = Http::remote($this->api_token, $this->url);
        $response = $http->post('functions/' . $func, $requests);

        $json = $response->json();
        $status = $response->status();

        return [$json, $status];
    }


    // post, get, patch, delete 等请求
    public function remoteRequest($method, $path, $requests)
    {
        $user = auth()->user();

        $http = Http::remote($this->api_token, $this->url)
            ->accept('application/json');

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

        $http = Http::remote($this->api_token, $this->url)
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
        $http = Http::remote($this->api_token, $this->url);
        $response = $http->post($path, $data);

        $json = $response->json();
        $status = $response->status();

        return [$json, $status];
    }

    public function check($module_id = null)
    {
        if ($module_id) {
            $module = Module::find($module_id);
        } else {
            $module = $this;
        }

        try {
            $http = Http::remote($module->api_token, $module->url);
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


    // // get cached modules
    // public static function cached_modules()
    // {
    //     return Cache::remember('modules', 600, function () {
    //         return Module::all();
    //     });
    // }

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
}
