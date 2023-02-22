@extends('layouts.admin')

@section('title', '修改 ' . $reply->workOrder->title . ' 的回复')

@section('content')
    <h3>修改 {{ $reply->workOrder->title }} 的回复</h3>

    @if (!$reply->is_pending)
        <form method="post" action="{{ route('admin.work-orders.replies.update', [$work_order, $reply]) }}">
            @csrf
            @method('PATCH')

            <x-markdown-editor name="content" :placeholder="$reply->content" :value="$reply->content"/>

            <button type="submit" class="btn btn-primary mt-3">修改</button>

        </form>

        <hr/>
        <form method="post" action="{{ route('admin.work-orders.replies.destroy', [$work_order, $reply]) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger mt-3">删除</button>
        </form>
    @else
        <p>回复状态为 推送中，无法修改。</p>
    @endif

@endsection
