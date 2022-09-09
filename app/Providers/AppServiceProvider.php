<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Alipay\EasySDK\Kernel\Config as AlipayConfig;
use Alipay\EasySDK\Kernel\Factory as AlipayFactory;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
            ])->baseUrl($url);
        });

        AlipayFactory::setOptions($this->alipayOptions());
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

        $options->appId = config('alipay.app_id');

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
