@php use Illuminate\Support\Facades\Auth; @endphp
    <!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="auto">

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
    <nav class="navbar navbar-expand-lg bd-navbar sticky-top bg-body" id="nav">
        <div class="container">
            <a class="navbar-brand" href="{{ route('admin.index') }}">
                管理员
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                <span class="bi bi-list fs-1"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                @auth('admin')
                    <!-- Left Side Of Navbar -->
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownUser" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                用户
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownUser">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">用户</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.hosts.index') }}">主机</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.notifications.create') }}">通知</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.user-groups.index') }}">用户组</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownSupport" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                服务
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownSupport">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.modules.index') }}">模块</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.hosts.index') }}">主机</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.work-orders.index') }}">工单</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.notifications.create') }}">通知</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.maintenances.index') }}">维护</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownSupport" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                系统
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownSupport">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.devices.index') }}">物联设备</a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.applications.index') }}">应用程序</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownSupport" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                管理
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownSupport">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.admins.index') }}">管理员</a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.transactions') }}">交易记录</a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.commands') }}">速查表</a>
                                </li>
                            </ul>
                        </li>

                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownCluster" role="button"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                集群
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarDropdownCluster">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.cluster.nodes') }}">
                                        节点
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.cluster.events') }}">
                                        广播
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.cluster.monitor') }}">
                                        监视器
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                @endauth

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

                            @php($admin = Auth::guard('admin')->user())

                            <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button"
                               data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {{ $admin->name ?? '管理员' }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('admin.admins.edit', $admin) }}">
                                    编辑资料
                                </a>

                                <a class="dropdown-item" href="{{ route('admin.logout') }}"
                                   onclick="document.getElementById('logout-form').submit();return false;">
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

    <div class="mt-5"></div>

</div>
</body>

</html>
