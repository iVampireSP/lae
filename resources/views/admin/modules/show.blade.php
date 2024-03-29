@extends('layouts.admin')

@section('title', '模块: ' . $module->name)

@section('content')
    <h3>{{ $module->name }}</h3>
    <p>状态: <x-module-status :status="$module->status"/>, 余额: {{ $module->balance }} 元。</p>

    <div class="mt-3">
        <a href="{{ route('admin.modules.edit', $module) }}">编辑</a>
        <a href="{{ route('admin.modules.allows', $module) }}">MQTT 授权</a>
        <a href="{{ route('admin.transactions') }}?module_id={{ $module->id }}">交易记录</a>
        <a href="{{ route('admin.modules.fast-login', $module) }}" target="_blank">快速登录</a>
        <a href="{{ route('admin.notifications.create') }}?user=all&module_id={{ $module->id }}">给此模块的所有用户发送通知</a>
    </div>


    <h4 class="mt-2">收益</h4>
    <div>
        <x-module-earning :module="$module"/>
    </div>

    <h4 class="mt-2">主机</h4>
    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>名称</th>
            <th>用户</th>
            <th>月估算价格</th>
            <th>状态</th>
            <th>更新时间</th>
            <th>创建时间</th>
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
                        {{ $host->name }}
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $host->user_id) }}"> {{ $host->user->name }}</a>
                    </td>
                    <td>
                        {{ $host->price }} 元
                    </td>
                    <td>
                        <x-host-status :status="$host->status"/>
                    </td>
                    <td>
                        {{ $host->updated_at }}
                    </td>
                    <td>
                        {{ $host->created_at }}
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
