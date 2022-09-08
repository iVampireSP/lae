<?php

namespace App\Models\Module;

use Illuminate\Support\Str;
use Illuminate\Auth\Authenticatable;
use Illuminate\Support\Facades\Http;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class Module extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

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
    public function remoteRequest($method, $func, $requests)
    {
        $http = Http::remote($this->api_token, $this->url)
            ->accept('application/json')
            ->withHeaders(['X-Func' => $func]);


        unset($requests['func']);

        $requests['user_id'] = auth('api')->id();

        $user = auth('api')->user();

        if ($method == 'post') {
            // add user to requests
            $requests['user'] = $user;
        }

        $requests['user_id'] = $user['id'];

        $response = $http->{$method}("functions/{$func}", $requests);

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
