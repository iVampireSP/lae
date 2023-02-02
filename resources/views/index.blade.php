@extends('layouts.app')

@section('content')

    @guest
        <p>嗨，游客</p>
        <p>您需要先 登录 / 注册，才能继续使用 莱云。</p>

        <p>如果您继续，则代表您已经阅读并同意 <a href="https://www.laecloud.com/tos/" target="_blank"
                                                    class="text-decoration-underline">服务条款</a></p>
        <a href="{{ route('login') }}" class="btn btn-primary">登录</a>
        <a href="{{ route('register') }}" class="btn btn-primary">注册</a>
    @endguest

    @auth
        @if(!auth('web')->user()->isRealNamed())
            <x-alert-danger>
                <div>
                    您还没有<a href="{{ route('real_name.create') }}">实人认证</a>，<a href="{{ route('real_name.create') }}">实人认证</a>后才能使用所有功能。
                </div>
            </x-alert-danger>
        @endif

        @if (session('token'))
            <x-alert-warning>
                <div>
                    像密码一样保管好您的 API Token。
                    <br/>
                    {{ session('token') }}
                </div>
            </x-alert-warning>
        @endif


        <p>嗨, {{ auth('web')->user()->name }}
        @php($user = auth('web')->user())
        <form method="POST" action="{{ route('users.update') }}">
            @csrf
            @method('PATCH')
            <div class="form-floating mb-2">
                <input type="text" class="form-control" placeholder="用户名"
                       aria-label="用户名" name="name" required maxlength="25"
                       value="{{ $user->name }}">
                <label>用户名</label>
            </div>

            <button type="submit" class="btn btn-primary">
                更新
            </button>
        </form>

        <p>在这里，你可以获取新的 Token 来对接其他应用程序或者访问 控制面板。</p>

        <form action="{{ route('token.new') }}" name="newToken" method="POST">
            @csrf
            <div class="input-group mb-3">
                <input type="text" class="form-control" id="token_name" name="token_name" placeholder="这个 Token 要用来做什么"
                       aria-label="Example text with button addon"
                       aria-describedby="button-addon1">
                <button class="btn btn-outline-primary" type="submit" id="button-addon1">生成</button>

            </div>


        </form>

        <hr/>
        <p>如果你需要撤销对所有应用程序的授权，你可以在这里吊销所有 Token</p>
        <form action="{{ route('token.delete_all') }}" method="post">
            @csrf
            @method('DELETE')
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
