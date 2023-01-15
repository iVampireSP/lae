<?php

namespace App\Support;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * 实名认证支持
 */
class RealNameSupport
{

    private string $url = 'https://faceidh5.market.alicloudapi.com';
    private string $app_code;

    private PendingRequest $http;

    public function __construct()
    {
        $this->app_code = config('settings.supports.real_name.code');

        $this->http = Http::withHeaders([
            'Authorization' => 'APPCODE ' . $this->app_code,
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
            'Accept' => 'application/json',
        ])->baseUrl($this->url);
    }

    /**
     * 创建实名认证请求
     *
     * @param $user_id
     * @param $name
     * @param $id_card
     *
     * @return string
     */
    public function create($user_id, $name, $id_card): string
    {
        $id = Str::random(32);

        Cache::remember('real_name:' . $id, 600, function () use ($user_id, $name, $id_card) {
            return [
                'user_id' => $user_id,
                'name' => $name,
                'id_card' => $id_card,
            ];
        });

        return $this->submit($id);
    }

    /** 向 实名认证服务 发送请求
     *
     * @param string $id
     *
     * @return string
     */
    private function submit(string $id): string
    {
        $real_name = Cache::get('real_name:' . $id);

        if (!$real_name) {
            abort(404, '找不到实名认证请求');
        }

        $data = [
            'bizNo' => $id,
            'idNumber' => $real_name['id_card'],
            'idName' => $real_name['name'],
            'pageTitle' => config('app.display_name') . ' 实名认证',
            'notifyUrl' => route('public.real-name.notify'),
            'procedureType' => 'video',
            'txtBgColor' => '#cccccc',

            'ocrIncIdBack' => 'false',
            'ocrOnly' => 'false',
            'pageBgColor' => 'false',
            'retIdImg' => 'false',
            'returnImg' => 'false',
            'returnUrl' => route('public.real-name.process'),
        ];

        $resp = $this->http->asForm()->post('/edis_ctid_id_name_video_ocr_h5', $data)->json();

        if (!$resp || $resp['code'] !== '0000') {
            abort(500, '调用远程服务器时出现了问题，请检查身份证号码是否正确。');
        }

        return $resp['verifyUrl'];
    }

    /**
     * 验证实名认证请求
     *
     * @param array $request
     *
     * @return array|bool
     */
    public function verify(array $request): array|bool
    {
        $data = json_decode($request['data'], true);

        $verify = $this->verifyIfSuccess($request['data'], $request['sign']);

        if (!$verify) {
            Log::debug('实名认证签名验证失败', $request);
            return false;
        }

        if ($data['code'] !== 'PASS') {
            return false;
        }

        $return = Cache::get('real_name:' . $data['bizNo'], false);

        Cache::forget('real_name:' . $data['bizNo']);

        return $return;
    }

    private function verifyIfSuccess(string $request, string $sign): bool
    {

        $public_key = <<<EOF
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEWKKJoLwh6XEBkTeCfVbKSB3zkkycbIdd8SBabj2jpWynXx0pBZvdFpbb9AEiyrnM8bImhpz8YOXc2yUuN1ui/w==
-----END PUBLIC KEY-----
EOF;

        $sign = base64_decode($sign);

        $public_key = openssl_pkey_get_public($public_key);

        if (!$public_key) {
            abort(500, '公钥错误');
        }

        $flag = openssl_verify($request, $sign, $public_key, OPENSSL_ALGO_SHA256);

        return $flag === 1;
    }

    public function getBirthday(string $id_card): string
    {
        $year = substr($id_card, 6, 4);
        $month = substr($id_card, 10, 2);
        $day = substr($id_card, 12, 2);

        return $year . '-' . $month . '-' . $day;
    }

}
