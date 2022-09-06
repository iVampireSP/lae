<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>支付结果</title>
</head>

<body>
    @if (session('success'))
        <p style="color:green">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p style="color:red">{{ session('error') }}</p>
    @endif

    <p>您现在可以关闭页面并继续刚才的操作了。</p>
</body>

</html>
