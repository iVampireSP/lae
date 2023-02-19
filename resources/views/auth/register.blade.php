@extends('layouts.app')

@section('content')
    <h2>加入 {{ config('app.display_name') }}</h2>

    <form action="{{ route('register') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="name" class="text-left ml-0">名称</label>
            <input id="name" type="text"
                   class="form-control @error('name') is-invalid @enderror" name="name"
                   value="{{ old('name') }}" required autocomplete="name" autofocus placeholder="账户显示的名称">

            @error('name')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="form-group">
            <label for="email" class="text-left ml-0">邮箱</label>
            <input id="email" type="text"
                   class="form-control @error('email') is-invalid @enderror" name="email"
                   value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="您的邮箱地址">

            @error('email')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="form-group mt-2">
            <label for="password">密码</label>
            <input type="password" id="password" name="password"
                   class="form-control rounded-right @error('password') is-invalid @enderror" required placeholder="密码">
            @error('password')
            <span class="invalid-feedback" role="alert">
                <strong>{{ $message }}</strong>
            </span>
            @enderror
        </div>

        <div class="form-group mt-2">
            <label for="password-confirm">确认密码</label>
            <input type="password" id="password-confirm" name="password_confirmation"
                   class="form-control rounded-right" required autocomplete="new-password" placeholder="再次输入您的密码">
        </div>


        <div class="text-start mt-3">如果您继续，则代表您已经阅读并同意
            <a
                href="https://www.laecloud.com/tos/"
                target="_blank"
                class="text-decoration-underline">服务条款</a>
            和
            <a
                href="https://www.laecloud.com/tos/"
                target="_blank"
                class="text-decoration-underline">隐私政策</a>。

            <br />
            在您注册后，我们将给您发一份验证邮件。如果您 3 天内没有验证，您的账号将被删除。

        </div>


        <button class="btn btn-primary btn-block mt-2" type="submit">
            注册
        </button>

    </form>

    <br/>

    <a class="link" href="{{ route('login') }}">
        {{ __('Login') }}
    </a>
    &nbsp;
    <a class="link" href="{{ route('password.request') }}">
        {{ __('Forgot Your Password?') }}
    </a>
@endsection
