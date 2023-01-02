@extends('layouts.admin')

@section('title', '回复: ' . $reply->workOrder->title)

@section('content')
    @if (!$reply->is_pending)
        <form method="post" action="{{ route('admin.work-orders.replies.update', [$work_order, $reply]) }}">
            @csrf
            @method('PATCH')

            {{--    编辑   --}}
            <div class="form-group">
                <label for="content">内容</label>
                <textarea name="content" id="content" class="form-control" rows="10">{{ $reply->content }}</textarea>
            </div>

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
