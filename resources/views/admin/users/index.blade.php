@extends('layouts.admin')

@section('title', '用户')

@section('content')

    {{--  搜索  --}}
    <div class="row">
        <div class="col-12">
            <form action="{{ route('admin.users.index') }}" method="get">
                <div class="form-row row">
                    <div class="col-2">
                        <input type="text" class="form-control" name="id" placeholder="用户 ID"
                               value="{{ request('id') }}" aria-label="用户 ID">
                    </div>
                    <div class="col-2">
                        <input type="text" class="form-control" name="name" placeholder="用户名"
                               value="{{ request('name') }}" aria-label="用户名">
                    </div>
                    <div class="col-2">
                        <input type="text" class="form-control" name="email" placeholder="邮箱"
                               value="{{ request('email') }}" aria-label="邮箱">
                    </div>
                    <div class="col-2">
                        <button type="submit" class="btn btn-primary">搜索</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- 用户列表 --}}
    <div class="overflow-auto">
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
                        <a href="{{ route('admin.users.edit', $user) }}" title="显示和编辑 {{ $user->name }} 的资料">
                            {{ $user->name }}
                        </a>
                    </td>
                    <td>
                        {{ $user->email }} @if(!$user->hasVerifiedEmail())
                            <small class="text-muted">没有验证</small>
                        @endif
                    </td>
                    <td>
                        @if ($user->hasBalance())
                            <span class="text-danger">{{ $user->balance }} 元</span>
                        @else
                            <span class="text-muted">{{ $user->balance }} 元</span>
                        @endif
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

    {{-- 分页 --}}
    {{ $users->links() }}

@endsection
