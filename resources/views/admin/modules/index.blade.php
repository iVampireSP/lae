@extends('layouts.admin')

@section('title', '模块')

@section('content')

    <h3>模块</h3>
    <a href="{{ route('admin.modules.create') }}">新建模块</a>
    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>名称</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($modules as $module)
                <tr>
                    <td>
                        <a href="{{ route('admin.modules.show', $module) }}">
                            {{ $module->id }}
                        </a>
                    </td>
                    <td>
                        {{ $module->name }}
                    </td>
                    <td>
                        <a href="{{ route('admin.modules.show', $module) }}" class="btn btn-primary btn-sm">查看</a>
                        <a href="{{ route('admin.modules.edit', $module) }}" class="btn btn-primary btn-sm">编辑</a>
                        <a href="{{ route('admin.modules.allows', $module) }}" class="btn btn-primary btn-sm">MQTT 授权</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $modules->links() }}

@endsection
