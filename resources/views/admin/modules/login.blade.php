@extends('layouts.admin')

@section('title', '快速登录')

@section('content')

    <h4>正在登录到 {{ $module->name }}...</h4>

    <form class="visually-hidden" action="{{ $resp['url'] }}" method="GET" id="fast-login">
        <input type="hidden" name="fast_login_token" value="{{ $resp['token'] }}"/>
    </form>

    <script>
        setTimeout(() => {
            document.getElementById('fast-login').submit();
        }, 1000)
    </script>

@endsection

