@extends('layouts.app')

@section('content')

    <h2>欢迎使用 {{ config('app.display_name') }}</h2>

    <form action="{{ route('login') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="email" class="text-left ml-0">邮箱</label>
            <input type="email" name="email" id="email" class="form-control mb-3" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">密码</label>
            <input type="password" id="password" name="password"
                   class="form-control rounded-right" required>
        </div>

        <div class="form-group mt-2">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" checked>
                <label class="form-check-label" for="remember">
                    记住登录
                </label>
            </div>
        </div>

        <div class="mt-1">如果您继续，则代表您已经阅读并同意 <a
                href="https://www.laecloud.com/tos/"
                target="_blank"
                class="text-decoration-underline">服务条款</a>
        </div>


        <button class="btn btn-primary btn-block mt-2" type="submit">
            登录
        </button>


    </form>

    <br/>

    <a class="link" href="{{ route('register') }}">
        {{ __('Register') }}
    </a>
    &nbsp;
    <a class="link" href="{{ route('password.request') }}">
        {{ __('Forgot Your Password?') }}
    </a>
@endsection
