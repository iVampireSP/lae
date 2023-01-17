<!doctype html>
<html lang="zh_CN" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '莱云')</title>


    <link rel="icon" href="/images/fav.jpg"/>
    <link rel="apple-touch-icon" href="/images/fav.jpg"/>

    <!-- Fonts -->
    {{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
<div id="app">
    <nav class="navbar navbar-expand-lg bd-navbar sticky-top bg-body">
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
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('index') }}">密钥管理</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('balances.index') }}">余额与充值</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('transfer') }}">转账</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('transactions') }}">交易记录</a>
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
                @if (!auth('web')->user()->isAdult())
                    <x-alert-warning>
                        未成年账号，需要家长或监护人的同意以及指导下才能使用莱云。
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
</body>

</html>
