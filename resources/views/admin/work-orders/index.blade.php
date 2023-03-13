@extends('layouts.admin')

@section('title', '工单')

@section('content')

    <h3>工单</h3>

    <a href="?status=open">开启的工单</a>
    <a href="?status=user_read">用户已读</a>
    <a href="?status=replied">已回复</a>
    <a href="?status=user_replied">用户已回复</a>
    <a href="?status=read">您已读</a>
    <a href="?status=on_hold">挂起</a>
    <a href="?status=in_progress">正在处理</a>
    <a href="?status=closed">已关闭</a>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>标题</th>
            <th>模块</th>
            <th>主机</th>
            <th>发起者</th>
            <th>创建时间</th>
            <th>状态</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($workOrders as $workOrder)
                <tr>
                    <td>
                        <a href="{{ route('admin.work-orders.show', $workOrder) }}">
                            {{ $workOrder->id }}
                        </a>
                    </td>
                    <td>
                        <a href="{{ route('admin.work-orders.show', $workOrder) }}">
                            {{ $workOrder->title }}
                        </a>
                    </td>
                    <td>
                        @if ($workOrder->module_id)
                            <a
                                href="{{ route('admin.modules.show', $workOrder->module_id) }}"
                                class="module_name"
                                module="{{ $workOrder->module_id }}">{{ $workOrder->module_id }}
                            </a>
                        @else
                            {{ config('app.display_name') }}
                        @endif
                    </td>
                    <td>
                        @if ($workOrder->host_id)
                            <a
                                href="{{ route('admin.hosts.edit', $workOrder->host_id) }}"
                                class="host_name"
                            >
                                {{ $workOrder->host?->name }}
                            </a>
                        @else
                            无
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $workOrder->user_id) }}">{{ $workOrder->user?->name }}</a>
                    </td>
                    <td>
                        {{ $workOrder->created_at }}, {{ $workOrder->created_at->diffForHumans() }}
                    </td>
                    <td>
                        <x-work-order-status :status="$workOrder->status"></x-work-order-status>
                    </td>
                    <td>
                        <a href="{{ route('admin.work-orders.edit', $workOrder) }}"
                           class="btn btn-primary btn-sm">编辑</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $workOrders->links() }}

@endsection
