<?php

declare(strict_types=1);

use Yansongda\Pay\Pay;

$alipay_app_private_key = env('ALIPAY_APP_SECERT_CERT_PATH', config_path('secrets/alipayAppPriv.key'));
if (!file_exists($alipay_app_private_key)) {
    $alipay_app_private_key = '';
} else {
    $alipay_app_private_key = trim(file_get_contents($alipay_app_private_key));
}

$wechat_pay_cert = env('WECHAT_PAY_CERT_PATH', config_path('secrets/wepay_cert.pem'));
$wechat_pay_private_key = env('WECHAT_PAY_PRIVATE_KEY_PATH', config_path('secrets/wepay_key.pem'));

return [
    'alipay' => [
        'default' => [
            // 必填-支付宝分配的 app_id
            'app_id' => env('ALIPAY_APP_ID'),
            // 必填-应用私钥 字符串或路径
            'app_secret_cert' => $alipay_app_private_key,
            // 必填-应用公钥证书 路径
            'app_public_cert_path' => env('ALIPAY_APP_PUBLIC_CERT_PATH', config_path('secrets/appCertPublicKey.crt')),
            // 必填-支付宝公钥证书 路径
            'alipay_public_cert_path' => env('ALIPAY_PUBLIC_CERT_PATH', config_path('secrets/alipayCertPublicKey_RSA2.crt')),
            // 必填-支付宝根证书 路径
            'alipay_root_cert_path' => env('ALIPAY_ROOT_CERT_PATH', config_path('secrets/alipayRootCert.crt')),
            'return_url' => env('ALIPAY_CALLBACK_RETURN_URL'),
            'notify_url' => env('ALIPAY_CALLBACK_NOTIFY_URL'),
            // 选填-服务商模式下的服务商 id，当 mode 为 Pay::MODE_SERVICE 时使用该参数
            'service_provider_id' => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SANDBOX, MODE_SERVICE
            // 如果应用程序环境为 local，自动切换为沙箱模式
            'mode' => env('APP_ENV') == 'local' ? Pay::MODE_SANDBOX : Pay::MODE_NORMAL,
        ],
    ],
    'wechat' => [
        'default' => [
            // 必填-商户号，服务商模式下为服务商商户号
            'mch_id' => env('WECHAT_MERCHANT_ID'),
            // 必填-商户秘钥
            'mch_secret_key' => env('WECHAT_V3_API_KEY'),
            // 必填-商户私钥 字符串或路径
            'mch_secret_cert' => $wechat_pay_private_key,
            // 必填-商户公钥证书路径
            'mch_public_cert_path' => $wechat_pay_cert,
            // 必填
            'notify_url' => env('WECHAT_PAY_CALLBACK_NOTIFY_URL'),
            // 选填-公众号 的 app_id
            'mp_app_id' => env('WECHAT_MP_APP_ID'),
            // 选填-小程序 的 app_id
            'mini_app_id' => '',
            // 选填-app 的 app_id
            'app_id' => '',
            // 选填-合单 app_id
            'combine_app_id' => '',
            // 选填-合单商户号
            'combine_mch_id' => '',
            // 选填-服务商模式下，子公众号 的 app_id
            'sub_mp_app_id' => '',
            // 选填-服务商模式下，子 app 的 app_id
            'sub_app_id' => '',
            // 选填-服务商模式下，子小程序 的 app_id
            'sub_mini_app_id' => '',
            // 选填-服务商模式下，子商户id
            'sub_mch_id' => '',
            // 选填-默认为正常模式。可选为： MODE_NORMAL, MODE_SERVICE
            'mode' => Pay::MODE_NORMAL,
        ],
    ],
    'unipay' => [
        'default' => [
            // 必填-商户号
            'mch_id' => '',
            // 必填-商户公私钥
            'mch_cert_path' => '',
            // 必填-商户公私钥密码
            'mch_cert_password' => '000000',
            // 必填-银联公钥证书路径
            'unipay_public_cert_path' => '',
            // 必填
            'return_url' => '',
            // 必填
            'notify_url' => '',
        ],
    ],
    'xunhu' => [
        'app_id' => env('XUNHU_PAY_APP_ID'),
        'app_secret' => env('XUNHU_PAY_APP_SECRET'),
        'gateway' => env('XUNHU_PAY_GATEWAY', 'https://api.xunhupay.com/payment/do.html'),
    ],
    'http' => [ // optional
        'timeout' => 5.0,
        'connect_timeout' => 5.0,
        // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
    ],
    // optional，默认 warning；日志路径为：sys_get_temp_dir().'/logs/yansongda.balances.log'
    'logger' => [
        'enable' => false,
        'file' => null,
        'level' => 'debug',
        'type' => 'single', // optional, 可选 daily.
        'max_file' => 30,
    ],
];
