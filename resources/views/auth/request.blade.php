@extends('layouts.app')

@section('title', '认证')

@section('content')

    <div class="row justify-content-evenly align-items-center" style="height: 80vh">
        <div class="text-center col-md-4 ">
            <span style="font-size: 10rem">
                <i class="bi bi-fingerprint"></i>
            </span>

            <h3>
                <code>
                    @if (isset($data['module']) && !is_null($data['module']))
                        <span>模块：{{ $data['module']['name'] }}</span>
                    @elseif (isset($data['applications']) && !is_null($data['application']))
                        <span>应用程序：{{ $data['application']['name'] }}</span>
                    @elseif (isset($data['from_user']) && !is_null($data['from_user']))
                        <span>来自用户：{{ $data['from_user']['name'] }}</span>
                    @else
                        <span>一个应用程序</span>
                    @endif

                </code>
                的验证请求
            </h3>
        </div>
        <div class="col-md-4">
            <div>
                <p>{{ $data['meta']['description'] }}</p>


                <br/>

                在您同意后，您的 <b>ID</b>, <b>UUID</b>, <b>昵称</b>, <b>邮件信息 和
                    实人认证成功的时间(不包含个人信息)</b>,
                <b>余额</b>,
                <b>用户组 ID</b> 将会被发送给它们。
                @if ($data['meta']['require_token'])
                    <br/>
                    你的 <b>Token</b> 将会新建一个，并发送给它们。
                @endif

                @if (isset($data['meta']['abilities']))
                    <div>
                        权限列表:
                        @foreach($data['meta']['abilities'] as $ability)
                            <b>{{ $ability }}</b>
                        @endforeach
                    </div>
                @endif

                @if (isset($data['meta']['return_url']))
                    <div>
                        返回地址: {{$data['meta']['return_url']}}
                    </div>
                @endif

                @auth('web')
                    <form method="POST" action="{{ route('auth_request.store') }}" class="mt-3">
                        @csrf
                        <input type="hidden" name="token" value="{{ $data['meta']['token'] }}">
                        <button type="submit" class="btn btn-primary">同意</button>
                    </form>
                @else
                    <p>
                        在继续之前，请先<a href="{{ route('login') }}">登录</a>或<a
                            href="{{ route('register') }}">注册</a>。
                    </p>
                @endauth
            </div>
        </div>
    </div>

@endsection
