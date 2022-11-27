<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', '管理员')</title>

    <!-- Fonts -->
    {{-- <link rel="dns-prefetch" href="//fonts.gstatic.com"> --}}
    {{-- <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet"> --}}

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body>
<div id="app">
    <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.index') }}">
                管理员
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <!-- Left Side Of Navbar -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.users.index') }}">用户</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.hosts.index') }}">主机</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.modules.index') }}">模块</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.work-orders.index') }}">工单</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.transactions') }}">交易记录</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.admins.index') }}">管理员</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.user-groups.index') }}">用户组</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.applications.index') }}">应用程序</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('admin.commands') }}">速查表</a>
                    </li>
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="navbar-nav ms-auto">
                    <!-- Authentication Links -->
                    @if (!Auth::guard('admin')->check())
                        @if (Route::has('admin.login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif
                    @else
                        @if (Auth::guard('web')->check())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('index') }}">切换到
                                    {{ Auth::guard('web')->user()->name }}</a>
                            </li>
                        @endif
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ Auth::guard('admin')->user()->email ?? '' }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('admin.logout') }}"
                                   onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST"
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
        <x-alert/>

        <div class="container">
            @yield('content')
        </div>
    </main>

    <x-module-script/>

</div>
</body>

</html>
