<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body>
    <p>在这里，你可以获取新的 Token 来对接其他应用程序。</p>

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
        <p style="color:green">{{ session('token') }}</p>
        {{-- <a href="http://localhost:3000/login?token={{ session('token') }}">前往</a> --}}
    @endif

    @guest
        <a href="{{ route('login') }}">登录</a>
    @else
        <form action="{{ route('createApiToken') }}" method="post">
            @csrf
            <input type="text" name="name" placeholder="Token 名称." required>
            <button>获取新的 Token</button>
        </form>

        <hr>
        <p>如果你需要撤销对所有应用程序的授权，你可以在这里吊销所有 Token</p>
        <form action="{{ route('invokeAllApiToken') }}" method="post">
            @csrf
            @method('delete')
            <button>吊销所有 Token</button>
        </form>
        <p>*如果您的 Token 被泄漏，您应该立即吊销所有 Token</p>
    @endguest
</body>

</html>
