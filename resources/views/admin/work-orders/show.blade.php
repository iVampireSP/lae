@extends('layouts.admin')

@section('title', '工单: ' . $workOrder->title)

@section('content')
    <h3>{{ $workOrder->title }}</h3>
    <p>
        UUID: {{ $workOrder->uuid }}
    </p>
    <a href="{{ route('admin.work-orders.edit', $workOrder) }}">编辑此工单</a>
    <a href="{{ route('admin.users.edit', $workOrder->user_id) }}">用户: {{ $workOrder->user->name }}</a>

    @if($workOrder->ip)
        <p>IP 地址: {{ $workOrder->ip }}</p>
    @endif

    @if($workOrder->host_id)
        <a href="{{ route('admin.hosts.edit', $workOrder->host_id) }}">主机: {{ $workOrder->host->name }}</a>
    @endif

    @if($workOrder->module_id)
        <a href="{{ route('admin.modules.show', $workOrder->module_id) }}">模块: {{ $workOrder->module->name }}</a>
    @endif

    <hr/>
    <p>@parsedown($workOrder->content)</p>
    <hr/>

    <x-work-order-status :status="$workOrder->status"></x-work-order-status>

    <div class="mt-3">
        <h4>对话记录</h4>

        @foreach($replies as $reply)
            <div class="card border-light mb-3 shadow">
                <div class="card-header d-flex w-100 justify-content-between">
                    @if ($reply->role === 'user')
                        @if ($reply->user)
                            <a href="{{ route('admin.users.edit', $reply->user) }}">{{ $reply->user->name }}</a>
                        @else
                            {{ $reply->name }}
                        @endif
                    @elseif ($reply->role === 'admin')
                        <span class="text-primary">{{ config('app.display_name') }} 的 {{ $reply->name }}</span>
                    @elseif ($reply->role === 'module')
                        <a href="{{ route('admin.modules.edit', $workOrder->module_id) }}">{{ $workOrder->module->name }}
                            @if ($reply->name)
                                的 {{ $reply->name }}
                            @endif
                        </a>
                    @elseif ($reply->role === 'guest')
                        {{ $reply->name }}
                    @endif


                    <span class="text-end">
                        @if($reply->is_pending)
                            <span class="badge bg-primary">投递中</span>
                        @endif

                        <a href="{{ route('admin.work-orders.replies.edit', [$workOrder, $reply]) }}">编辑</a>
                        {{ $reply->created_at }}
                    </span>
                </div>

                <div class="card-body">
                    @parsedown($reply->content)
                </div>

                @if($reply->ip)

                    <div class="card-footer">
                        <span>IP 地址: {{ $reply->ip }}</span>
                    </div>
                @endif

            </div>
        @endforeach

        {{ $replies->links() }}
    </div>

    <h4 class="mt-3">您的回复</h4>
    <form method="POST" action="{{ route('admin.work-orders.replies.store', $workOrder->id) }}">
        @csrf
        {{-- label --}}
        <div class="form-group">
            <label for="content">内容</label>
            <textarea class="form-control" id="content" name="content" rows="10"
                      placeholder="作为 {{ config('app.display_name') }} 的 {{ Auth::guard('admin')->user()->name }} 回复。"></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3 mb-3">提交</button>
    </form>

@endsection
