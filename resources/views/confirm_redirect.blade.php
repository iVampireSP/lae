@extends('layouts.app')

@section('title', '确认')

@section('content')

    @if (session('callback'))

        @if(session('token'))

            <div style="height: 80vh; display: flex" class="justify-content-center align-items-center">
                <div>
                    <i class="bi bi-back" style="font-size: 10rem"></i>
                    <br/>
                    <p class="text-center fs-3">
                        正在返回
                    </p>
                </div>
            </div>

            @php
                session()->forget('callback');
                session()->forget('referer.domain');
            @endphp

            <script>
                setTimeout(function () {
                    window.location.href = "{{ $callback . '?token=' . session('token')}}";
                }, 100);
            </script>
        @else

            <h3>您确定吗？</h3>
            <p>一个应用程序正在试图自动获取您的 Token，若您信任它，请点击 "授权"。</p>

            <p>您点击"好"后，您将前往这个地址: <code>{{ $callback }}</code>。</p>

            <form action="{{ route('token.new') }}" name="newToken" method="POST">
                @csrf
                <input type="hidden" name="name" placeholder="Token 名字"
                       value="自动登录 - {{ date('Y-m-d H:i:s') }}"/>

                @if($referer_host)
                    <input type="hidden" name="domain" value="{{ $referer_host }}"/>
                @endif

                <button type="submit" class="btn btn-primary">授权</button>

                <a href="/" class="btn btn-danger">不</a>

            </form>

        @endif
    @else

        <h3>嗯...还没有快捷登录。</h3>
        <p>您可以返回应用重试登录，或者继续做您的事情。</p>

    @endif

@endsection
