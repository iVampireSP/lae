@extends('layouts.admin')

@section('title', '工单: ' . $workOrder->title)

@section('content')

    <h3>{{ $workOrder->title }}</h3>
    <x-work-order-status :status="$workOrder->status"></x-work-order-status>
    <a href="{{ route('admin.work-orders.show', $workOrder) }}">查看工单</a>


    @if ($workOrder->status !== 'pending')
        <form method="post" action="{{ route('admin.work-orders.update', $workOrder) }}">
            @csrf
            @method('PATCH')

            {{--    修改状态    --}}
            <div class="form-group">
                <label for="status" class="col-sm-2 col-form-label">状态</label>
                <select class="form-control" id="status" name="status">
                    <option value="open" {{ $workOrder->status == 'open' ? 'selected' : '' }}>已开启</option>
                    <option value="closed" {{ $workOrder->status == 'closed' ? 'selected' : '' }}>关闭</option>
                    <option value="user_read" {{ $workOrder->status == 'user_read' ? 'selected' : '' }}>用户已读
                    </option>
                    <option value="user_replied" {{ $workOrder->status == 'user_replied' ? 'selected' : '' }}>用户已回复
                    </option>

                    <option value="replied" {{ $workOrder->status == 'replied' ? 'selected' : '' }}>已回复</option>
                    <option value="read" {{ $workOrder->status == 'read' ? 'selected' : '' }}>已读</option>

                    <option value="in_progress" {{ $workOrder->status == 'in_progress' ? 'selected' : '' }}>处理中
                    </option>
                </select>
            </div>

            {{--    启用通知    --}}
            <div class="form-group">
                <label for="notify" class="col-sm-2 col-form-label">通知</label>
                <select class="form-control" id="notify" name="notify">
                    <option value="1">是</option>
                    <option value="0" @if(!$workOrder->notify) selected @endif>否</option>
                </select>
            </div>

            <button type="submit" class="btn btn-primary mt-3">修改</button>

        </form>

        <hr/>
        <form method="post" action="{{ route('admin.work-orders.destroy', $workOrder) }}">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger mt-3">删除</button>
        </form>
    @else
        <p>工单状态为 推送中，无法修改。</p>
    @endif

@endsection
