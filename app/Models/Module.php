<?php

namespace App\Models;

use App\Exceptions\User\BalanceNotEnoughException;
use GeneaLabs\LaravelModelCaching\CachedBuilder;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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
        'status',
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

    // post, get, patch, delete 等请求
    public function remote($func, $requests): array
    {
        $response = $this->http()->post('functions/'.$func, $requests);

        return $this->getResponse($response);
    }

    public function http($files = []): PendingRequest
    {
        $http = Http::module($this->api_token, $this->url.'/remote');

        if ($files) {
            $http->asMultipart();
            foreach ($files as $name => $file) {
                $http->attach($name, $file['content'], $file['name']);
            }
        }

        $http->acceptJson()->timeout(5);

        if ($this->ip_port) {
            // 如果设置了 ip_port 则使用 ip_port
            $http->baseUrl($this->ip_port.'/remote');

            // 添加 Host 头
            $http->withHeaders([
                'Host' => parse_url($this->url, PHP_URL_HOST),
            ]);
        }

        return $http;
    }

    private function getResponse(Response $response): array
    {
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

    /**
     * @param    $method
     * @param    $path
     * @param    $requests
     * @param  array  $files
     * @return array
     */
    public function request($method, $path, $requests, array $files = []): array
    {
        try {
            return $this->baseRequest($method, "functions/$path", $requests, $files);
        } /** @noinspection PhpRedundantCatchClauseInspection */ catch (ConnectException|ConnectionException $e) {
            Log::error('在执行 call '.$method.' '.$path.' 时发生错误: '.$e->getMessage());

            return [
                'body' => null,
                'json' => null,
                'status' => 502,
                'success' => false,
            ];
        }
    }

    public function baseRequest($method, $path, $requests = [], $files = []): array
    {
        $user = auth('sanctum')->user();

        $http = $this->http($files);

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

    public function whereHasBalance(string $amount = '0'): self|Builder|CachedBuilder
    {
        return $this->where('balance', '>=', $amount);
    }

    public function isUp(): bool
    {
        return $this->status === 'up';
    }

    public function isDown(): bool
    {
        return $this->status === 'down';
    }

    public function isMaintenance(): bool
    {
        return $this->status === 'maintenance';
    }
}
