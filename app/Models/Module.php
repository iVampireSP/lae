<?php

namespace App\Models;

use App\Exceptions\User\BalanceNotEnoughException;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;

class Module extends Authenticatable
{
    use Cachable;

    public $incrementing = false;

    protected $table = 'modules';

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'api_token',
    ];

    protected $hidden = [
        'api_token',
        'wecom_key',
        'balance',
        'url',
    ];

    protected $casts = [
        'id' => 'string',
        'balance' => 'decimal:4',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function (self $model) {
            if (! app()->environment('local')) {
                $model->api_token = Str::random(60);
            }

            // 如果设置了 url 并且结尾有 / 则去掉
            if ($model->url) {
                $model->url = rtrim($model->url, '/');
            }
        });
        static::updating(function (self $model) {
            // 如果结尾有 / 则去掉
            $model->url = rtrim($model->url, '/');
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
        $response = $this->http()->post('functions/'.$func, $requests);

        return $this->getResponse($response);
    }

    public function http(): PendingRequest
    {
        return Http::module($this->api_token, $this->url.'/remote')->acceptJson()->timeout(5);
    }

    private function getResponse(Response $response): array
    {
        // $module_token = $response->header('x-module-api-token');

        $success = true;
        $json = $response->json();
        $status = $response->status();

        // if status code is not 20x
        if ($status < 200 || $status >= 300) {
            $success = false;

            // 防止误删除
            // if ($module_token !== $this->api_token) {
            //     $this->status = 'maintenance';
            //     $this->save();
            //
            //     $status = 401;
            // }
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
        return $this->baseRequest($method, "functions/$path", $requests);
    }

    public function baseRequest($method, $path, $requests = []): array
    {
        $user = auth('sanctum')->user();

        $http = $this->http();
        if ($user) {
            $http = $http->withHeaders([
                'X-User-Id' => $user->id,
            ]);

            // $requests['user_id'] = $user->id;
            // if ($method == 'post') {
            //     // add user to requests
            //     $requests['user'] = $user;
            // }
        }

        $response = $http->{$method}($path, $requests);

        return $this->getResponse($response);
    }

    public function moduleRequest($method, $path, $requests): array
    {
        $module_id = auth('module')->id();

        $http = $this->http()->withHeaders([
            'X-Module' => $module_id,
        ]);

        $requests['module_id'] = $module_id;

        $response = $http->{$method}("exports/$path", $requests);

        return $this->getResponse($response);
    }

    public function check(): bool
    {
        // $module_id = null
        // if ($module_id) {
        //     $module = Module::find($module_id);
        // } else {
        //     $module = $this;
        // }

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

    #[ArrayShape(['transactions' => 'array'])]
    public function calculate(): array
    {
        $cache_key = 'module_earning_'.$this->id;

        return Cache::get($cache_key, []);
    }

    /**
     * 扣除费用
     *
     * @param  string|null  $amount
     * @param  string|null  $description
     * @param  bool  $fail
     * @param  array  $options
     * @return string
     */
    public function reduce(string|null $amount = '0', string|null $description = '消费', bool $fail = false, array $options = []): string
    {
        if ($amount === null || $amount === '') {
            return $this->balance;
        }

        Cache::lock('module_balance_'.$this->id, 10)->block(10, function () use ($amount, $fail, $description, $options) {
            $this->refresh();

            if ($this->balance < $amount) {
                if ($fail) {
                    throw new BalanceNotEnoughException();
                }
            }

            $this->balance = bcsub($this->balance, $amount, 4);
            $this->save();

            if ($description) {
                $data = [
                    'module_id' => $this->id,
                    'amount' => $amount,
                    'description' => $description,
                    'payment' => 'balance',
                    'type' => 'payout',
                ];

                if ($options) {
                    $data = array_merge($data, $options);
                }

                (new Transaction)->create($data);
            }
        });

        return $this->balance;
    }

    /**
     * 增加余额
     *
     * @param  string|null  $amount
     * @param  string  $payment
     * @param  string|null  $description
     * @param  array  $options
     * @return string
     */
    public function charge(string|null $amount = '0', string $payment = 'console', string|null $description = '充值', array $options = []): string
    {
        if ($amount === null || $amount === '') {
            return $this->balance;
        }

        Cache::lock('module_balance_'.$this->id, 10)->block(10, function () use ($amount, $description, $payment, $options) {
            $this->refresh();

            $this->balance = bcadd($this->balance, $amount, 4);

            $this->save();

            if ($description) {
                $data = [
                    'module_id' => $this->id,
                    'amount' => $amount,
                    'payment' => $payment,
                    'description' => $description,
                    'type' => 'income',
                ];

                if ($options) {
                    $data = array_merge($data, $options);
                }

                (new Transaction)->create($data);
            }
        });

        return $this->balance;
    }

    public function hasBalance(string $amount = '0'): bool
    {
        return bccomp($this->balance, $amount, 4) >= 0;
    }

    public function whereHasBalance(string $amount = "0"): self|Builder|CachedBuilder
    {
        return $this->where('balance', '>=', $amount);
    }
}
