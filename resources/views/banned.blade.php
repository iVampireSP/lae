<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>您已被封禁</title>
</head>

<body>
    <h1>很抱歉，您可能违反了我们的规定。</h1>
    <p>{{ auth()->user()->banned_reason }}</p>

    <a href="{{ route('logout') }}">更换账号</a>
</body>

</html>
