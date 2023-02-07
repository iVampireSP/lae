@extends('layouts.app')

@section('title', '认证')

@section('content')

    <h3>
        <code>
            @if (isset($data['module']) && !is_null($data['module']))
                )
                <span>模块：{{ $data['module']['name'] }}</span>
            @endif
            @if (isset($data['applications']) && !is_null($data['application']))
                <span>应用程序：{{ $data['application']['name'] }}</span>
            @endif
            @if (isset($data['from_user']) && !is_null($data['from_user']))
                <span>来自用户：{{ $data['from_user']['name'] }}</span>
            @endif
        </code>
        想要获取你的用户信息。
    </h3>

    <p>{{ $data['description'] }}</p>

    @auth('web')
        <form method="POST" action="{{ route('auth_request.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $data['token'] }}">
            <button type="submit" class="btn btn-primary">同意</button>
        </form>
    @else
        <p>
            在继续之前，请先<a href="{{ route('login') }}">登录</a>或<a href="{{ route('register') }}">注册</a>。
        </p>
    @endauth

@endsection
