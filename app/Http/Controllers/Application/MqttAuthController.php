<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MqttAuthController extends Controller
{
    //

    public function authentication(Request $request)
    {
        //
        $client_id = $request->input('client_id');
        $username = $request->input('username');
        $password = $request->input('password');


        //    分割 username
        $usernames = explode('.', $username);

        $module_id = $usernames[0] ?? null;
        $device_id = $usernames[1] ?? null;


        $module = Module::where('id', $module_id)->first();

        if (!$module) {
            return $this->ignore();
        }

        // 如果没有设置 device_id，那么就是模块自己的连接
        if (!$device_id) {
            // 让 api_token 可见
            $module->makeVisible('api_token');

            // 比较 api_token
            if ($module->api_token == $password) {
                return $this->allow();
            } else {
                return $this->deny();
            }
        } else {
            // 如果设置了 device_id，那么就是设备的连接

            // 此时，我们得联系模块，让模块去验证设备。

            $response = $module->baseRequest('post', 'mqtt/authentication', [
                'client_id' => $client_id,
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

    public function authorization(Request $request)
    {
        // 禁止订阅保留的
        if ($request->input('topic') == '$SYS/#') {
            return $this->deny();
        }

        $action = $request->input('action');
        $client_id = $request->input('client_id');
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

        $module = Module::where('id', $module_id)->first();

        if (!$module) {
            // 不属于我们管理，跳过。
            // Log::debug('不属于我们管理，跳过。');
            return $this->ignore();
        }


        // 设备只能在自己的模块下发布消息
        if ($action == 'publish') {
            if ($topics[0] !== $module_id) {
                // Log::debug('设备只能在自己的模块下发布消息');
                return $this->deny();
            }
        }

        if (count($usernames) === 1) {
            // 是模块自己的连接
            return $this->allow();
        }

        // Log::debug('联系模块。');

        // 联系模块，让模块去验证设备授权。
        $response = $module->baseRequest('post', 'mqtt/authorization', [
            'client_id' => $client_id,
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

    private function deny()
    {
        return response([
            'result' => 'deny',
        ], 200);
    }

    private function ignore()
    {
        return response([
            'result' => 'ignore',
        ], 200);
    }

    private function allow()
    {
        return response([
            'result' => 'allow',
            'is_superuser' => false,
        ], 200);
    }
}
