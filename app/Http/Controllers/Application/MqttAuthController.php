<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleAllow;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MqttAuthController extends Controller
{
    public function authentication(Request $request): Response
    {
        $client_id = explode('.', $request->input('client_id'));

        $username = $request->input('username');
        $usernames = explode('.', $username);

        $password = $request->input('password');

        $module_id = $usernames[0] ?? null;
        $device_id = $usernames[1] ?? null;

        $module = (new Module)->where('id', $module_id)->first();

        if (! $module) {
            return $this->ignore();
        }

        if ($client_id[0] !== $module->id) {
            return $this->ignore();
        }

        // 如果没有设置 device_id，那么就是模块自己的连接
        if (! $device_id) {
            // 让 api_token 可见
            $module->makeVisible('api_token');

            // 比较 api_token
            if ($module->api_token == $password) {
                return $this->allow();
            } else {
                return $this->deny();
            }
        } else {
            // 如果设置了 device_id，那么就是设备的连接，此时，我们得联系模块，让模块去验证设备。

            // 设备必须有两段 ID
            if (count($client_id) < 2) {
                return $this->ignore();
            }

            $response = $module->baseRequest('post', 'mqtt/authentication', [
                'client_id' => $client_id[1],
                'device_id' => $device_id,
                'password' => $password,
            ]);

            if ($response['status'] === 200) {
                return $this->allow();
            } else {
                return $this->deny();
            }
        }
    }

    private function ignore(): Response
    {
        return response([
            'result' => 'ignore',
        ], 200);
    }

    private function allow(): Response
    {
        return response([
            'result' => 'allow',
            'is_superuser' => false,
        ], 200);
    }

    private function deny(): Response
    {
        return response([
            'result' => 'deny',
        ], 200);
    }

    public function authorization(Request $request): Response
    {
        // 禁止订阅保留的
        if ($request->input('topic') == '$SYS/#') {
            return $this->deny();
        }

        $client_id = explode('.', $request->input('client_id'));
        if (count($client_id) < 2) {
            return $this->deny();
        }

        $action = $request->input('action');

        $username = $request->input('username');
        $topic = $request->input('topic');

        if ($topic === '#') {
            return $this->deny();
        }

        // 使用 / 分割 topic
        $topics = explode('/', $topic);

        $usernames = explode('.', $username);

        $module_id = $usernames[0] ?? null;
        $device_id = $usernames[1] ?? null;

        $module = (new Module)->where('id', $module_id)->first();

        if (! $module) {
            // 不属于我们管理，跳过。
            return $this->ignore();
        }

        // 设备只能在自己的模块下发布消息
        if ($action == 'publish') {
            if ($topics[0] !== $module_id) {
                // 但是，在拒绝之前，应该检查一下，是否有允许的模块
                $allow = (new ModuleAllow)->where('module_id', $topics[0])->where('allowed_module_id', $module_id)->exists();

                if (! $allow) {
                    return $this->deny();
                }
            }
        }

        if (count($usernames) === 1) {
            // 是模块自己的连接
            return $this->allow();
        }

        // 其他情况，让模块去验证

        // 联系模块，让模块去验证设备授权。
        $response = $module->baseRequest('post', 'mqtt/authorization', [
            'client_id' => $client_id[1],
            'device_id' => $device_id,
            'type' => $action,
            'topic' => $topic,
        ]);

        if ($response['status'] === 200) {
            return $this->allow();
        } else {
            return $this->deny();
        }
    }
}
