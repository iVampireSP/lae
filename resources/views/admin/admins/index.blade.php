@extends('layouts.admin')

@section('title', '管理员')

@section('content')

    <h3>管理员</h3>
    <p>权力越大，责任越大</p>
    <a href="{{ route('admin.admins.create') }}">新建管理员</a>
    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>名称</th>
            <th>邮件</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($admins as $admin)
                <tr>
                    <td>
                        <a href="{{ route('admin.admins.edit', $admin) }}">
                            {{ $admin->id }}
                        </a>
                    </td>
                    <td>
                        {{ $admin->name }}
                    </td>
                    <td>
                        {{ $admin->email }}
                    </td>
                    <td>
                        <a href="{{ route('admin.admins.edit', $admin) }}" class="btn btn-primary btn-sm">编辑</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $admins->links() }}

@endsection
