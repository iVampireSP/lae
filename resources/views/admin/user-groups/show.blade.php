@extends('layouts.admin')

@section('title', '用户组: ' . $user_group->name)

@section('content')
    <h3>{{ $user_group->name }}</h3>
    <a href="{{ route('admin.user-groups.edit', $user_group) }}">编辑此用户组</a>

    <br/>
    <span>
        此用户组的 ID 为 {{ $user_group->id }}。
        @if ($user_group->discount == 100)
            不享受折扣
        @else
            享受 {{ $user_group->discount }}% 的折扣
        @endif
        ，
        @if ($user_group->exempt)
            并且有暂停 / 终止豁免权
        @else
            并且没有暂停 / 终止豁免权
        @endif
        。
    </span>

    {{-- 用户列表 --}}
    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>用户名</th>
            <th>邮箱</th>
            <th>余额</th>
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
@endsection
