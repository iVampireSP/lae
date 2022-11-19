@extends('layouts.admin')

@section('title', '模块')

@section('content')

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
                        <a href="{{ route('admin.modules.edit', $module) }}" class="btn btn-primary btn-sm">编辑</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $modules->links() }}





@endsection