@extends('layouts.app')

@section('content')
    <h3>欢迎使用 {{ config('app.display_name') }}</h3>
    <p>您需要先 登录 / 注册，才能继续使用 {{ config('app.display_name') }}。</p>

    <p>如果您继续，则代表您已经阅读并同意 <a href="https://www.laecloud.com/tos/" target="_blank"
                                            class="text-decoration-underline">服务条款</a></p>
    <a href="{{ route('login') }}" class="btn btn-primary">登录</a>
    <a href="{{ route('register') }}" class="btn btn-primary">注册</a>
@endsection
