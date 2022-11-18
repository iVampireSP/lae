@extends('layouts.admin')

@section('title', '主机')

@section('content')

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>模块</th>
            <th>名称</th>
            <th>创建时间</th>
            <th>更新时间</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($hosts as $host)
                <tr>
                    <td>
                        <a href="{{ route('admin.hosts.show', $host) }}">
                            {{ $host->id }}
                        </a>
                    </td>
                    <td>
                        <span class="module_name" module="{{ $host->module_id }}">{{ $host->module_id }}</span>
                    </td>
                    <td>
                        {{ $host->name }}
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
