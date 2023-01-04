<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
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
            'node' => [
                'type' => config('settings.node.type'),
                'id' => config('settings.node.id'),
                'ip' => config('settings.node.ip'),
            ],
            'data' => $data,
        ]));
    }

    public static function hset($key, $value, $data = []): void
    {
        Redis::hset($key, $value, json_encode($data));
    }

    public static function get($key, $default = null): string|array|null
    {
        return Cache::get(self::$prefix . $key, $default);
    }

    public static function set($key, $value, $ttl = null): void
    {
        Cache::put(self::$prefix . $key, $value, $ttl);
    }

    public static function forget($key): void
    {
        Cache::forget(self::$prefix . $key);
    }

    // forever
    public static function forever($key, $value): void
    {
        Cache::forever(self::$prefix . $key, $value);
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
        ]);

        Cluster::publish('node_init');
    }
}
