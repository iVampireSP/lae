<!doctype html>
<html lang="zh_CN">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '莱云')</title>

    <!-- Fonts -->
{{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
{{-- <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

<!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
<div id="app">
    <nav class="navbar navbar-expand-md shadow-sm bg-white">
        <div class="container">
            <a class="navbar-brand text-auto" href="{{ route('index') }}">
                {{ config('app.display_name') }}
            </a>
            <button class="navbar-toggler text-auto" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-auto" href="{{ route('index') }}">密钥</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-auto" href="{{ route('balances.index') }}">余额</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-auto" href="{{ route('transfer') }}">转账</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-auto" href="{{ route('transactions') }}">交易记录</a>
                    </li>

                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    @if (Auth::guard('admin')->check())
                        <li class="nav-item">
                            <a class="nav-link"
                               href="{{ route('admin.users.edit', Auth::guard('web')->id()) }}">返回到 {{ Auth::guard('admin')->user()->email }}</a>
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
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
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
            <x-alert/>
        </div>

        <div class="container">
            @yield('content')
        </div>
    </main>
</div>
</body>

</html>
