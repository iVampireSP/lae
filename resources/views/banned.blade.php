@extends('layouts.app')

@section('title', '您已被封禁')

@section('content')

    @if (auth()->user()->banned_at)
        <h3>很抱歉，您可能违反了我们的规定。</h3>
        <p>{{ auth()->user()->banned_reason }}</p>

        <form id="logout-form" action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="btn btn-primary">退出登录</button>
        </form>
    @else
        <h3>您的账号正常。</h3>
        <a href="{{ route('index') }}">返回首页</a>

    @endif

@endsection
