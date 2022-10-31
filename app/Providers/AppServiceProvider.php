<?php

namespace App\Providers;

use Illuminate\Support\Facades\Http;
// use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //

        require_once app()->basePath('app') . '/Helpers.php';
    }

    public function boot()
    {
        $this->generateInstanceId();


        // $this->app->make(\Illuminate\Notifications\ChannelManager::class)->extend('your-channel', function () {
        //     return $this->app->make(App\Channels\YourChannel::class);
        // });

        //

        // header('server: Cluster Ready!');
        // header('x-powered-by: LaeCloud');
        // header('x-for-you: Code is Poetry.');


        Http::macro('remote', function ($api_token, $url) {
            // 关闭证书验证
            return Http::withoutVerifying()->withHeaders([
                'X-Remote-Api-Token' => $api_token,
                'Content-Type' => 'application/json'
            ])->withOptions([
                'version' => 2,
            ])->baseUrl($url);
        });

        // $wechat_pay_config = [
        //     'mch_id' => config('payment.wepay.mch_id'),

        //     // 商户证书
        //     'private_key' => __DIR__ . '/certs/apiclient_key.pem',
        //     'certificate' => __DIR__ . '/certs/apiclient_cert.pem',

        //     // v3 API 秘钥
        //     'secret_key' =>
        //     config('payment.wepay.v3_secret_key'),

        //     // v2 API 秘钥
        //     'v2_secret_key' => config('payment.wepay.v2_secret_key'),

        //     // 平台证书：微信支付 APIv3 平台证书，需要使用工具下载
        //     // 下载工具：https://github.com/wechatpay-apiv3/CertificateDownloader
        //     'platform_certs' => [
        //         // '/path/to/wechatpay/cert.pem',
        //     ],

        //     /**
        //      * 接口请求相关配置，超时时间等，具体可用参数请参考：
        //      * https://github.com/symfony/symfony/blob/5.3/src/Symfony/Contracts/HttpClient/HttpClientInterface.php
        //      */
        //     'http' => [
        //         'throw'  => true, // 状态码非 200、300 时是否抛出异常，默认为开启
        //         'timeout' => 5.0,
        //     ],
        // ];

        // $app = new WePay($wechat_pay_config);

        // // mount app to global
        // app()->instance('wepay', $app);
    }


    public function generateInstanceId()
    {
        if (config('app.instance_id') == null) {
            $instance_id = uniqid();

            // 获取 .env 目录
            $env_path = dirname(__DIR__) . '/../.env';

            // 追加到 .env 文件
            file_put_contents($env_path, PHP_EOL . "INSTANCE_ID={$instance_id}", FILE_APPEND);

            // 重新加载配置
            config(['app.instance_id' => $instance_id]);

            // $env = file_get_contents(app()->environmentFilePath());
            // $env = preg_replace('/INSTANCE_ID=(.*)/', 'INSTANCE_ID=' . $instance_id, $env);
            // file_put_contents(app()->environmentFilePath(), $env);

        }
    }
}
