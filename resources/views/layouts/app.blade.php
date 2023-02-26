<!doctype html>
<html lang="zh_CN" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '莱云')</title>


    <link rel="icon" href="/images/lae-fav.png"/>
    <link rel="apple-touch-icon" href="/images/lae-fav.png"/>

    <!-- Fonts -->
    {{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
<div id="app">
    <nav class="navbar navbar-expand-lg bd-navbar sticky-top bg-body" id="nav">
        <div class="container">
            <a class="navbar-brand" href="{{ route('index') }}">
                {{ config('app.display_name') }}
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="bi bi-list fs-1"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    @auth('web')

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('index') }}">授权</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('balances.index') }}">资金</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('hosts.index') }}">主机</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transfer') }}">转账</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('transactions') }}">记录</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('affiliates.index') }}">推介</a>
                        </li>
                    @endauth

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('maintenances') }}">维护</a>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <a class="nav-link" target="_blank"
                       href="{{ config('settings.dashboard.base_url') }}">仪表盘</a>

                    <a class="nav-link" target="_blank"
                       href="{{ config('settings.forum.base_url') }}">社区</a>

                    <a class="nav-link"
                       href="{{ route('contact') }}">联系我们</a>

                    @if (Auth::guard('admin')->check())

                        <li class="nav-item">

                            @if(Auth::guard('web')->check())
                                <a class="nav-link"
                                   href="{{ route('admin.users.edit', Auth::guard('web')->id()) }}">回到 {{ Auth::guard('admin')->user()->name }}</a>
                            @else
                                <a class="nav-link"
                                   href="{{ route('admin.index') }}">切换到后台</a>
                            @endif
                        </li>

                    @endif

                    <!-- Authentication Links -->
                    @guest
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">{{ __('Login') }}</a>
                        </li>

                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::user()->name }}
                            </a>


                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('password.request') }}">
                                    {{ __('Reset Password') }}
                                </a>


                                @if (!session('auth.password_confirmed_at'))
                                    <a class="dropdown-item" href="{{ route('password.confirm') }}">
                                        进入 Sudo 模式
                                    </a>
                                @else
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="document.getElementById('exit-sudo-form').submit();return false;">
                                        退出 Sudo 模式
                                    </a>

                                    <form id="exit-sudo-form" action="{{ route('sudo.exit') }}" method="POST"
                                          class="d-none">
                                        @csrf
                                    </form>
                                @endif


                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="document.getElementById('logout-form').submit();return false;">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                      class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">
            @auth('web')
                @if (!auth('web')->user()->hasVerifiedEmail())
                    <x-alert-warning>
                        请先 <a href="{{ route('verification.notice') }}">验证您的邮箱</a>。
                    </x-alert-warning>
                @endif
            @endauth
        </div>

        <x-alert/>

        <div class="container">

            @yield('content')
        </div>
    </main>
</div>

<script>
    const nav = document.getElementById('nav');

    @if (session('auth.password_confirmed_at'))
        nav.style.backgroundColor = 'rgb(234 234 234 / 9%)';
    nav.classList.remove('bg-body');

    @endif
</script>

</body>

</html>
