<?php

namespace App\Support;

use Illuminate\Support\Facades\Redis;

class ClusterSupport
{
    public static string $prefix = 'cluster:';

    public static function isCluster(): bool
    {
        return self::isMaster() || self::isSlave();
    }

    public static function isMaster(): bool
    {
        return config('settings.node.type') === 'master';
    }

    public static function isSlave(): bool
    {
        return config('settings.node.type') === 'slave';
    }

    public static function publish($event, $data = [], $register = false): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
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

        if ($register) {
            self::registerThisNode(false);
        }
    }

    public static function registerThisNode($report = true, $data = []): void
    {
        $node_id = config('settings.node.id');
        
        $node = [
            'type' => config('settings.node.type'),
            'id' => $node_id,
            'ip' => config('settings.node.ip'),
            'last_heartbeat' => time(),
        ];

        $node = array_merge($node, $data);

        self::updateThisNode($node);

        if ($report) {
            ClusterSupport::publish('node.ok');
        }
    }

    public static function updateThisNode($data = []): void
    {
        $node_id = config('settings.node.id');

        $node = self::hget('nodes', $node_id, "[]");
        $node = json_decode($node, true);

        $node = array_merge($node, $data);

        ClusterSupport::hset('nodes', $node_id, $node);
    }


    public static function hset($key, $value, $data = []): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Redis::hset(self::$prefix . $key, $value, json_encode($data));
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
            // echo $message;
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

    public static function get($key, $default = null): string|array|null
    {
        /** @noinspection PhpUndefinedMethodInspection */
        return Redis::get(self::$prefix . $key, $default);
    }

    public static function forget($key): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Redis::forget(self::$prefix . $key);
    }

    // forever
    public static function forever($key, $value): void
    {
        self::set($key, $value, -1);
    }

    public static function set($key, $value, $ttl = null): void
    {
        /** @noinspection PhpUndefinedMethodInspection */
        Redis::set(self::$prefix . $key, $value, $ttl);
    }

    public static function hget($key, $hashKey, $default = []): string|array|null
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $value = Redis::hget(self::$prefix . $key, $hashKey);

        return $value ?: $default;
    }

    public static function nodes($hide_ip = false): array
    {
        $nodes = self::hgetAll('nodes');

        $append_nodes = [];

        foreach ($nodes as $key => $node) {
            $nodes[$key] = json_decode($node, true);

            if ($hide_ip) {
                unset($nodes[$key]['ip']);
            }

            $append_nodes[] = $nodes[$key];
        }

        return $append_nodes;
    }

    public static function hgetAll($hashKey, $default = []): array
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $value = Redis::hgetall(self::$prefix . $hashKey);

        return $value ?: $default;
    }

    public static function currentNode()
    {
        return config('settings.node');
    }
}
