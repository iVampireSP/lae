@extends('layouts.admin')

@section('title', '工单: ' . $workOrder->title)

@section('content')

    <form method="post" action="{{ route('admin.work-orders.update', $workOrder) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="title" class="col-sm-2 col-form-label">标题</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ $workOrder->title }}">
        </div>

{{--        <div class="form-group">--}}
{{--            <textaera name="title">--}}
{{--                {{ $workOrder->title }}--}}
{{--            </textaera>--}}
{{--        </div>--}}



        <button type="submit" class="btn btn-primary mt-3">修改</button>

    </form>

@endsection
