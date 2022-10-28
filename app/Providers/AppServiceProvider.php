<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Alipay\EasySDK\Kernel\Config as AlipayConfig;
use Alipay\EasySDK\Kernel\Factory as AlipayFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use EasyWeChat\Pay\Application as WePay;

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

        AlipayFactory::setOptions($this->alipayOptions());

        $wechat_pay_config = [
            'mch_id' => config('payment.wepay.mch_id'),

            // 商户证书
            'private_key' => __DIR__ . '/certs/apiclient_key.pem',
            'certificate' => __DIR__ . '/certs/apiclient_cert.pem',

            // v3 API 秘钥
            'secret_key' =>
            config('payment.wepay.v3_secret_key'),

            // v2 API 秘钥
            'v2_secret_key' => config('payment.wepay.v2_secret_key'),

            // 平台证书：微信支付 APIv3 平台证书，需要使用工具下载
            // 下载工具：https://github.com/wechatpay-apiv3/CertificateDownloader
            'platform_certs' => [
                // '/path/to/wechatpay/cert.pem',
            ],

            /**
             * 接口请求相关配置，超时时间等，具体可用参数请参考：
             * https://github.com/symfony/symfony/blob/5.3/src/Symfony/Contracts/HttpClient/HttpClientInterface.php
             */
            'http' => [
                'throw'  => true, // 状态码非 200、300 时是否抛出异常，默认为开启
                'timeout' => 5.0,
            ],
        ];

        $app = new WePay($wechat_pay_config);

        // mount app to global
        app()->instance('wepay', $app);
    }

    private function alipayOptions()
    {
        $options = new AlipayConfig();
        $options->protocol = 'https';

        // if local
        if (app()->environment() == 'local') {
            $options->gatewayHost = 'openapi.alipaydev.com';
        } else {
            $options->gatewayHost = 'openapi.alipay.com';
        }

        $options->signType = 'RSA2';

        $options->appId = config('payment.alipay.app_id');

        // 为避免私钥随源码泄露，推荐从文件中读取私钥字符串而不是写入源码中
        $options->merchantPrivateKey = trim(Storage::get('alipayAppPriv.key'));

        $options->alipayCertPath = storage_path('app/alipayCertPublicKey_RSA2.crt');
        $options->alipayRootCertPath = storage_path('app/alipayRootCert.crt');
        $options->merchantCertPath = storage_path('app/appCertPublicKey.crt');

        //注：如果采用非证书模式，则无需赋值上面的三个证书路径，改为赋值如下的支付宝公钥字符串即可
        // $options->alipayPublicKey = Storage::get('alipayCertPublicKey_RSA2.crt');

        //可设置异步通知接收服务地址（可选）
        $options->notifyUrl = route('balances.notify');


        return $options;
    }
}
