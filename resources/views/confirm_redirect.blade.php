@extends('layouts.app')

@section('title', '确认')

@section('content')

    @if (session('callback'))

        @if(session('token'))

            <h3>带你去目标站点...</h3>

            @php
                session()->forget('callback');
            @endphp

            <script>
                setTimeout(function () {
                    window.location.href = "{{ $callback . '?token=' . session('token')}}";
                }, 1000);
            </script>
        @else

            <h3>您确定吗？</h3>
            <p>一个应用程序正在试图自动获取您的 Token， 诺您信任它，请点击"好"。</p>

            <p>您点击"好"后，您将前往这个地址: <code>{{ $callback }}</code>。</p>


            <form action="{{ route('newToken') }}" name="newToken" method="POST">
                @csrf
                <input type="hidden" name="token_name" placeholder="Token 名字"
                       value="自动登录 - {{ date('Y-m-d H:i:s') }}"/>
                <button type="submit" class="btn btn-primary">好</button>

                <a href="/" class="btn btn-danger">不，带我去首页。</a>

            </form>

        @endif
    @else

        <h3>嗯...还没有快捷登录。</h3>
        <p>您可以返回应用重试登录，或者继续做您的事情。</p>

    @endif

@endsection
