@extends('layouts.app')

@section('title', '付款')

@section('content')

    <h1>莱云 支付处理器</h1>
    <p>此页面正在进行充值交易。</p>

    <div id="pay">
        <p>您将要给 {{ $balance->user->name }} 充值 {{ $balance->amount }} 元。
            @if ($balance->user == auth()->user())
                这是您自己的账号。
            @else
                但是请看仔细，<span class="text-danger">这不是您自己的账号。</span>
            @endif

        </p>

        <p>如果您已知晓情况，请
            @php
                if ($balance->payment === 'alipay') {
                    $payment = '支付宝';
                } elseif ($balance->payment === 'wechat') {
                    $payment = '微信';
                } else {
                    $payment = '相应的软件';
                }

            @endphp
            使用"{{ $payment }}"扫描二维码。
        </p>


        {{ $qr_code }}
    </div>

    <h3 class="text-success d-none" id="pay-success">您已成功完成支付。@auth
            我们将稍后带您去余额界面。
        @else
            您可以关闭此网页去通知 {{ $balance->user->name }} 了。
        @endauth</h3>
    <h3 class="text-danger d-none" id="pay-error">此支付出现了问题。@auth
            我们将稍后带您去余额界面。稍后请尽快联系我们。
        @else
            您可以让 {{ $balance->user->name }} 联系我们，或者您可以点击上方的"联系我们"按钮。
        @endauth</h3>


    <script>
        const inter = setInterval(function () {
            axios.get(location.href)
                .then(function (response) {
                    if (response.data.paid_at) {
                        document.getElementById('pay-success').classList.remove('d-none');
                        document.getElementById('pay').classList.add('d-none');

                        clearInterval(inter);

                        @auth
                        setTimeout(function () {
                            location.href = '/balances';
                        }, 3000);
                        @endauth
                    }
                })
                .catch(function () {
                    document.getElementById('pay-error').classList.remove('d-none');
                    document.getElementById('pay').classList.add('d-none');

                    @auth
                    setTimeout(function () {
                        location.href = '/balances';
                    }, 3000);
                    @endauth

                    clearInterval(inter);
                });
        }, 1500);
    </script>

@endsection
