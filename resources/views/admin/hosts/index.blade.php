@extends('layouts.admin')

@section('title', '主机')

@section('content')

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>模块</th>
            <th>名称</th>
            <th>用户</th>
            <th>月估算价格</th>
            <th>创建时间</th>
            <th>更新时间</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($hosts as $host)
                <tr>
                    <td>
                        <a href="{{ route('admin.hosts.edit', $host) }}">
                            {{ $host->id }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('admin.modules.show', $host->module_id) }}" class="module_name"
                           module="{{ $host->module_id }}">{{ $host->module_id }}</a>
                    </td>
                    <td>
                        {{ $host->name }}
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $host->user_id) }}"> {{ $host->user->name }}</a>
                    </td>
                    <td>
                        @if ($host->managed_price !== null)
                            <span class="text-danger">{{ $host->managed_price }}</span>
                        @else
                            {{ $host->price }} 元
                        @endif
                    </td>
                    <td>
                        {{ $host->created_at }}
                    </td>
                    <td>
                        {{ $host->updated_at }}
                    </td>
                    <td>
                        <a href="{{ route('admin.hosts.edit', $host) }}" class="btn btn-primary btn-sm">编辑</a>
                    </td>
                </tr>

            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $hosts->links() }}

@endsection
