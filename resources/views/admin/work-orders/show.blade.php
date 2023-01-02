@extends('layouts.admin')

@section('title', '工单: ' . $workOrder->title)

@section('content')
    <h3>{{ $workOrder->title }}</h3>
    <a href="{{ route('admin.work-orders.edit', $workOrder) }}">编辑此工单</a>
    <h5>{{ \Illuminate\Mail\Markdown::parse($workOrder->content) }}</h5>

    <x-work-order-status :status="$workOrder->status"></x-work-order-status>

    <div class="mt-3">
        <h4>对话记录</h4>

        @foreach($replies as $reply)
            <div class="card border-light mb-3 shadow">
                <div class="card-header d-flex w-100 justify-content-between">
                    @if ($reply->role === 'user')
                        @if ($reply->user_id)
                            <a href="{{ route('admin.users.edit', $reply->user) }}">{{ $workOrder->user->name }}</a>
                        @else
                            {{ $reply->name }}
                        @endif
                    @elseif ($reply->role === 'admin')
                        <span class="text-primary">{{ config('app.display_name') }}</span>
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
                        <a href="{{ route('admin.work-orders.replies.edit', [$workOrder, $reply]) }}">编辑</a>
                        {{ $reply->created_at }}
                    </span>
                </div>

                <div class="card-body">
                    {{ \Illuminate\Mail\Markdown::parse($reply->content) }}
                </div>
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
                      placeholder="作为 {{ config('app.display_name') }} 回复。"></textarea>
        </div>

        <button type="submit" class="btn btn-primary mt-3 mb-3">提交</button>
    </form>

@endsection
