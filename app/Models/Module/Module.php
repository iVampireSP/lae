<?php

namespace App\Models\Module;

use Http;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Module extends Authenticatable
{
    use HasFactory;

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

        $requests['user_id'] = auth('sanctum')->id();

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
