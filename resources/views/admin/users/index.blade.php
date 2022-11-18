@extends('layouts.admin')

@section('title', '用户')

@section('content')

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
                        <a href="{{ route('admin.users.edit', $user) }}" title="显示和编辑 {{ $user->name }} 的资料">
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

    {{-- 分页 --}}
    {{ $users->links() }}


@endsection
