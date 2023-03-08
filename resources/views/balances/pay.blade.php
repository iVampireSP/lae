@extends('layouts.app')

@section('title', '付款')

@section('content')

    <style>
        .success-icon {
            font-size: 10rem;
            color: #096dff
        }
    </style>


    <div class="d-flex justify-content-center align-items-center h-screen" style="height: 60vh">
        <div class="text-center">
            <div id="pay">
                @php
                    if ($balance->payment === 'alipay') {
                        $payment = '支付宝';
                    } elseif ($balance->payment === 'wechat') {
                        $payment = '微信';
                    } else {
                        $payment = '相应的软件';
                    }
                @endphp

                <h3>请使用 "{{ $payment }}" 扫描二维码。</h3>


                <div class="mt-3">
                    {{ $qr_code }}
                </div>

                <h3 class="mt-2"> {{ $balance->amount }} 元</h3>
            </div>

            <div class="d-none" id="pay-success">
                <div class="success-icon">
                    <i class="bi bi-check2-all"></i>
                </div>

                <h2>您已支付</h2>
                @auth
                    <p>我们收到了您的支付，谢谢！</p>
                @else
                    <p>您可以关闭此网页去通知 {{ $balance->user->name }} 了。</p>
                @endauth
            </div>
            <div class="text-danger d-none" id="pay-error">此支付出现了问题。@auth
                    我们将稍后带您去余额界面。稍后请尽快联系我们。
                @else
                    您可以让 {{ $balance->user->name }} 联系我们，或者您可以点击上方的"联系我们"按钮。
                @endauth
            </div>
        </div>
    </div>


    <script>
        const inter = setInterval(function () {
            axios.get(location.href)
                .then(function (response) {
                    if (response.data.paid_at ?? null) {
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
