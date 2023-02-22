@extends('layouts.admin')

@section('title', '通知')

@section('content')
    <h3>通知</h3>


    @if (Request::isNotFilled('user_id'))
        <h5>首先，我们得先筛选出要通知哪些用户。</h5>

        <form action="#" method="get">
            <div class="form-group">
                <label for="user">用户</label>
                <select name="user" id="user" class="form-control">
                    <option value="all" @if(Request::get('user') == 'all') selected @endif>全部</option>
                    <option value="normal" @if(Request::get('user') == 'normal') selected @endif>正常没有被封禁的用户
                    </option>
                    <option value="active" @if(Request::get('user') == 'active') selected @endif>有主机的用户</option>
                    <option value="banned" @if(Request::get('user') == 'banned') selected @endif>封禁的用户</option>
                </select>
            </div>

            <div class="form-group">
                <label for="module_id">在哪些模块拥有主机的</label>
                <select name="module_id" id="module_id" class="form-control">
                    <option value="">无</option>
                    @foreach ($modules as $module)
                        <option value="{{ $module->id }}"
                                @if(Request::get('module_id') == $module->id) selected @endif>{{ $module->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <label class="form-check-label" for="receive_marketing_email">
                        接收营销邮件的用户
                    </label>
                    <input class="form-check-input" type="checkbox" name="receive_marketing_email" id="receive_marketing_email" value="1" @if(Request::get('receive_marketing_email') == 1) checked @endif>
                </div>
            </div>

            <p>这两个搜搜条件只能二选一。</p>

            <button type="submit" class="btn btn-primary mt-1">筛选并确定条件</button>
        </form>

    @endif

    @if (count($users))
        <h5 class="mt-4">筛选出的用户，接下来我们得选择通知方式。</h5>
        {{-- 用户列表 --}}
        <div class="overflow-auto mt-3">
            <table class="table table-hover">
                <thead>
                <th>ID</th>
                <th>用户名</th>
                <th>邮箱</th>
                <th>余额</th>
                <th>用户组</th>
                <th>注册时间</th>
                <th>操作</th>
                </thead>

                <tbody>
                @foreach ($users as $user)
                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" title="切换到 {{ $user->name }}">
                                {{ $user->id }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user) }}"
                               title="显示和编辑 {{ $user->name }} 的资料">
                                {{ $user->name }}
                            </a>
                        </td>
                        <td>
                            {{ $user->email }}
                        </td>
                        <td>
                            {{ $user->balance }} 元
                        </td>
                        <td>
                            @if ($user->user_group_id)
                                <a href="{{ route('admin.user-groups.show', $user->user_group_id) }}">
                                    {{ $user->user_group->name }}
                                </a>
                            @else
                                无
                            @endif
                        </td>
                        <td>
                            {{ $user->created_at }}
                        </td>
                        <td>
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary btn-sm">编辑</a>
                        </td>
                    </tr>

                @endforeach
                </tbody>
            </table>
        </div>

        {{ $users->links() }}

        <form method="POST" action="{{ route('admin.notifications.store')}}">
            @csrf

            <input type="hidden" name="user" value="{{ Request::get('user') }}">
            <input type="hidden" name="module_id" value="{{ Request::get('module_id') }}">
            <input type="hidden" name="user_id" value="{{ Request::get('user_id') }}">

            <div class="form-check mt-1">
                <label class="form-check-label" for="send_mail">
                    邮件通知
                </label>
                <input class="form-check-input" type="checkbox" name="send_mail" id="send_mail" value="1"  @if(Request::get('send_mail') == 1) checked @endif>
            </div>

            <div class="form-group">
                <label for="title">标题</label>
                <input type="text" name="title" id="title" class="form-control" value="{{ old('title') }}">
            </div>

            <div class="form-group mt-4">
                <label for="content">通知内容 支持 Markdown</label>
                <textarea name="content" id="content" class="form-control" rows="10">{{ old('content') }}</textarea>
            </div>

            <button type="submit" class="btn btn-primary mt-3">发送</button>
            <span class="text-muted d-block">通知一旦发送，将无法撤销！</span>
        </form>

    @else
        <h5 class="mt-4">没有符合条件的用户。</h5>
    @endif
@endsection
