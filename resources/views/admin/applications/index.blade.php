@extends('layouts.admin')

@section('title', '应用程序')

@section('content')
    <h3>应用程序</h3>
    <p>要为外部程序服务，你需要先创建一个应用程序。</p>

    <a href="{{ route('admin.applications.create') }}">新建应用</a>
    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>名称</th>
            <th>描述</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($applications as $application)
                <tr>
                    <td>
                        <a href="{{ route('admin.applications.show', $application) }}">
                            {{ $application->id }}
                        </a>
                    </td>
                    <td>
                        {{ $application->name }}
                    </td>
                    <td>
                        {{ $application->description }}
                    </td>
                    <td>
                        <a href="{{ route('admin.applications.edit', $application) }}"
                           class="btn btn-primary btn-sm">编辑</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $applications->links() }}

@endsection
