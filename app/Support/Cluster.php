<?php

namespace App\Support;

use Illuminate\Support\Facades\Redis;

class Cluster
{

    public static string $prefix = 'cluster:';

    public static function isMaster(): bool
    {
        return config('settings.node.type') === 'master';
    }

    public static function isSlave(): bool
    {
        return config('settings.node.type') === 'slave';
    }

    public static function isCluster(): bool
    {
        return self::isMaster() || self::isSlave();
    }

    public static function publish($event, $data = []): void
    {
        Redis::publish('cluster_ready', json_encode([
            'event' => $event,
            'node' => [
                'type' => config('settings.node.type'),
                'id' => config('settings.node.id'),
                'ip' => config('settings.node.ip'),
                'last_heartbeat' => time(),
            ],
            'data' => $data,
        ]));
    }

    /**
     * @param string|array $events      事件名称
     * @param              $callback    callable 回调函数，接收一个参数，为事件数据。
     * @param              $ignore_self bool 是否忽略此节点的消息。
     *
     * @return void
     */
    public static function listen(string|array $events, callable $callback, bool $ignore_self = true): void
    {
        // socket timeout
        ini_set('default_socket_timeout', -1);

        Redis::subscribe('cluster_ready', function ($message) use ($events, $callback, $ignore_self) {
            $message = json_decode($message, true);

            if ($ignore_self && $message['node']['id'] === config('settings.node.id')) {
                return;
            }

            if (is_array($events)) {
                if (in_array($message['event'], $events)) {
                    $callback($message['event'], $message);
                }
            } else {
                if ($events === '*' || $events === $message['event']) {
                    $callback($message['event'], $message);
                }
            }
        });
    }

    public static function hset($key, $value, $data = []): void
    {
        Redis::hset(self::$prefix . $key, $value, json_encode($data));
    }

    public static function get($key, $default = null): string|array|null
    {
        return Redis::get(self::$prefix . $key, $default);
    }

    public static function set($key, $value, $ttl = null): void
    {
        Redis::set(self::$prefix . $key, $value, $ttl);
    }

    public static function forget($key): void
    {
        Redis::forget(self::$prefix . $key);
    }

    // forever
    public static function forever($key, $value): void
    {
        self::set($key, $value, -1);
    }

    public static function hget($key, $value, $default = null): string|array|null
    {
        return Redis::hget($key, $value, $default);
    }

    public static function registerThisNode(): void
    {
        $node_id = config('settings.node.id');

        Cluster::hset('nodes', $node_id, [
            'type' => config('settings.node.type'),
            'id' => $node_id,
            'ip' => config('settings.node.ip'),
            // utc +8 timestamp
            'last_heartbeat' => time(),
        ]);

        Cluster::publish('node.ok');
    }
}
