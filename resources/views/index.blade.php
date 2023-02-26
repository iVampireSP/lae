@extends('layouts.app')

@section('content')
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

        <div class="form-floating mb-2">
            <input type="text" class="form-control" placeholder="授权的域名"
                   aria-label="授权的域名" name="domain" maxlength="255">
            <label>授权的域名</label>
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
                    <p>
                        营销邮件订阅: <span class="user-select-none">
                            <a
                                onclick="update_receive_marketing_email()" style="cursor: pointer"
                                class="text-decoration-underline"></a>
                            <span id="receive_marketing_email_append_text"></span>
                        </span>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">好
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>

        let receive_marketing_email = {{ $user->receive_marketing_email ? 'true' : 'false' }};
        let receive_marketing_email_append_text = document.querySelector('#receive_marketing_email_append_text');

        function update_receive_marketing_email_text() {
            let ele = document.querySelector('a[onclick="update_receive_marketing_email()"]');

            if (receive_marketing_email) {
                ele.innerText = '是';
                receive_marketing_email_append_text.innerText = '';
            } else {
                receive_marketing_email_append_text.innerText = '。创业不易，感谢理解。';
                ele.innerText = '否';
            }
        }

        function update_receive_marketing_email() {
            axios.patch("{{route('users.update')}}", {
                receive_marketing_email: !receive_marketing_email
            }).then(response => {
                receive_marketing_email = response.data['receive_marketing_email']

                update_receive_marketing_email_text(receive_marketing_email)
            }).finally(() => {
                update_receive_marketing_email_text()
            })
        }

        update_receive_marketing_email_text()

    </script>

@endsection
