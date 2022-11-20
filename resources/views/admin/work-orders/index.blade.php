@extends('layouts.admin')

@section('title', '工单')

@section('content')

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>标题</th>
            <th>模块</th>
            <th>主机</th>
            <th>发起者</th>
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
                        <a
                            href="{{ route('admin.modules.show', $workOrder->module_id) }}"
                            class="module_name"
                            module="{{ $workOrder->module_id }}">{{ $workOrder->module_id }}
                        </a>
                    </td>
                    <td>
                        @if ($workOrder->host_id)
                            <a
                                href="{{ route('admin.hosts.edit', $workOrder->host_id) }}"
                                class="host_name"
                                host="{{ $workOrder->host_id }}">{{ $workOrder->host_id }}
                            </a>
                        @else
                            无
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $workOrder->user_id) }}">{{ $workOrder->user->name }}</a>
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
