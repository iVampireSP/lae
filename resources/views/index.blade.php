@extends('layouts.app')

@section('content')

    @if(!auth('web')->user()->isRealNamed())
        <x-alert-danger>
            <div>
                您还没有<a href="{{ route('real_name.create') }}">实人认证</a>，<a
                    href="{{ route('real_name.create') }}">实人认证</a>后才能使用所有功能。
            </div>
        </x-alert-danger>
    @endif

    @if (session('token'))
        <x-alert-warning>
            <div>
                像密码一样保管好您的 API Token。
                <br/>
                {{ session('token') }}
            </div>
        </x-alert-warning>
    @endif


    <h3>嗨, <span class="link" data-bs-toggle="modal" data-bs-target="#userInfo"
                  style="cursor: pointer">{{ auth('web')->user()->name }}</span></h3>
    @php($user = auth('web')->user())
    <form method="POST" action="{{ route('users.update') }}">
        @csrf
        @method('PATCH')
        <div class="form-floating mb-2">
            <input type="text" class="form-control" placeholder="用户名"
                   aria-label="用户名" name="name" required maxlength="25"
                   value="{{ $user->name }}">
            <label>用户名</label>
        </div>

        <button type="submit" class="btn btn-primary visually-hidden">
            更新
        </button>
    </form>

    <h3 class="mt-3">访问密钥</h3>
    <p>在这里，你可以获取新的 Token 来对接其他应用程序或者访问 控制面板。</p>

    <form action="{{ route('token.new') }}" name="newToken" method="POST">
        @csrf
        <div class="form-floating mb-2">
            <input type="text" class="form-control" placeholder="Token 名称"
                   aria-label="密钥名称" name="name" required maxlength="25">
            <label>Token 名称</label>
        </div>

        <button type="submit" class="btn btn-primary visually-hidden">
            创建
        </button>
    </form>

    <h3 class="mt-3">撤销密钥</h3>
    <p>如果你需要撤销对所有应用程序的授权，你可以在这里吊销所有 Token</p>
    <form action="{{ route('token.delete_all') }}" method="post">
        @csrf
        @method('DELETE')
        <button class="btn btn-danger" type="submit">撤销所有</button>
    </form>


    <div class="modal fade" id="userInfo" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $user->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>ID: {{ $user->id }}</p>
                    <p>Email: {{ $user->email }}</p>
                    @if ($user->birthday_at)
                        <p>年龄: {{ $user->birthday_at->age . ' 岁' }}</p>
                    @endif
                    <p>注册时间: {{ $user->created_at }}</p>
                    <p>验证时间: {{ $user->email_verified_at }}</p>
                    @if ($user->real_name_verified_at)
                        <p>实人认证时间: {{ $user->real_name_verified_at }}</p>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">好
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection
