@extends('layouts.app')

@section('content')

    @guest
        <p>嗨，游客</p>
        <p>您需要先登录，才能继续使用 莱云。</p>

        <p>如果您继续登录，则代表您已经阅读并同意 <a href="https://www.laecloud.com/tos/" target="_blank"
                                                    class="text-decoration-underline">服务条款</a></p>
        <a href="{{ route('login') }}" class="btn btn-primary">登录</a>
    @endguest

    @auth
        @if(!auth('web')->user()->real_name_verified_at)
            <x-alert-danger>
                <div>
                    由于监管不佳，我们的 镜缘映射 机器收到了来自服务商的违规违法通知且已被封禁。
                    <br />
                    2023.01.15 起，我们将开始加强监管，将实名认证升级为 实人认证，以及开始对 镜缘映射 隧道内容进行半自动化核查。
                    <br/>
                    <a href="{{ route('real_name.create') }}">点击这里实人认证</a>
                </div>
            </x-alert-danger>
        @endif

        @if (session('token'))
            <p style="color:green">这是新的 Token，请妥善保管：{{ session('token') }}</p>
            {{-- <a href="http://localhost:3000/login?token={{ session('token') }}">前往</a> --}}
        @endif


        <p>嗨, {{ auth('web')->user()->name }}
        <p>在这里，你可以获取新的 Token 来对接其他应用程序或者访问 控制面板。</p>

        <form action="{{ route('newToken') }}" name="newToken" method="POST">
            @csrf
            <label for="token_name">Token 名称</label>
            <input type="text" name="token_name" id="token_name" placeholder="Token 名字"/>
            <button class="btn btn-primary" type="submit">获取新的 Token</button>
        </form>

        <hr/>
        <p>如果你需要撤销对所有应用程序的授权，你可以在这里吊销所有 Token</p>
        <form action="{{ route('deleteAll') }}" method="post">
            @csrf
            @method('delete')
            <button class="btn btn-danger" type="submit">吊销所有 Token</button>
        </form>
        <p class="text-danger">*如果您的 Token 被泄漏，您应该立即吊销所有 Token</p>

        <hr/>
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button class="btn btn-danger" type="submit">退出登录</button>
        </form>
    @endauth

@endsection
