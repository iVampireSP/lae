@extends('layouts.admin')

@section('title', '维护计划')

@section('content')

    <h3>维护计划</h3>
    <a class="mt-3" href="{{ route('admin.maintenances.create') }}">添加计划</a>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
                <th>ID</th>
                <th>名称</th>
                <th>模块</th>
                <th>开始于</th>
                <th>结束于</th>
                <th>操作</th>
            </thead>

            <tbody>
                @foreach ($maintenances as $m)
                    <tr>
                        <td>
                            <a href="{{ route('admin.maintenances.edit', $m) }}">
                                {{ $m->id }}
                            </a>
                        </td>

                        <td>
                            <a href="{{ route('admin.maintenances.edit', $m) }}">
                                {{ $m->name }}
                            </a>
                        </td>

                        <td>
                            {{ $m->module?->name }}
                        </td>

                        <td>
                            {{ $m->start_at }}
                        </td>

                        <td>
                            {{ $m->end_at }}
                        </td>

                        <td>
                            <a href="{{ route('admin.maintenances.edit', $m) }}" class="btn btn-primary btn-sm">编辑</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
