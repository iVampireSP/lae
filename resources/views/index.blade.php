<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    {{-- display all errors --}}
    @if ($errors->any())
        <div style="color: red">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif
    @if (session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    @if (session('token'))
        <p style="color:green">这是新的 Token，请妥善保管：{{ session('token') }}</p>
        {{-- <a href="http://localhost:3000/login?token={{ session('token') }}">前往</a> --}}
    @endif

    @guest
        <p>嗨，游客</p>
        <p>您需要先登录，才能使用 莱云 Access Token Manager.</p>

        <a href="{{ route('login') }}">登录</a>
    @endguest


    @auth
        <p>嗨, {{ auth('web')->user()->name }}
        <p>在这里，你可以获取新的 Token 来对接其他应用程序。</p>

        <form action="{{ route('newToken') }}" name="newToken" method="POST">
            @csrf
            <input type="text" name="token_name" placeholder="Token 名字" />
            <button>获取新的 Token</button>
        </form>

        <hr />
        <p>如果你需要撤销对所有应用程序的授权，你可以在这里吊销所有 Token</p>
        <form action="{{ route('deleteAll') }}" method="post">
            @csrf
            @method('delete')
            <button>吊销所有 Token</button>
        </form>
        <p>*如果您的 Token 被泄漏，您应该立即吊销所有 Token</p>

        <hr />
        <p>退出登录</p>
        <form action="{{ route('logout') }}" method="post">
            @csrf
            <button>退出登录</button>
        </form>
    @endauth
</body>

</html>
