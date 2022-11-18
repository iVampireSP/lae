@extends('layouts.admin')

@section('title', '工单: ' . $workOrder->title)

@section('content')
    <h3>{{ $workOrder->title }}</h3>
    <h5>{{ \Illuminate\Mail\Markdown::parse($workOrder->content) }}</h5>

    <x-work-order-status :status="$workOrder->status"></x-work-order-status>

    <p>在这里，您无法回复工单，只能够查看。</p>
    <div class="mt-3">
        <!-- replies -->
        <h4>对话记录</h4>

        @foreach($replies as $reply)
            <div class="card border-light mb-3 shadow">
                <div class="card-header">
                    @if ($reply->user_id)
                        <a href="{{ route('admin.users.edit', $reply->user) }}">{{ $workOrder->user->name }}</a>
                    @else
                        <a href="{{ route('admin.modules.edit', $workOrder->module_id) }}">{{ $workOrder->module->name }}</a>
                    @endif

                    <span class="text-end">{{ $reply->created_at }}</span>
                </div>

                <div class="card-body">
                    {{ \Illuminate\Mail\Markdown::parse($reply->content) }}
                </div>
            </div>
        @endforeach
    </div>
@endsection
