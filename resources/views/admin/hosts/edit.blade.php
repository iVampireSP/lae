@extends('layouts.admin')

@section('title', '主机:' . $host->name)

@section('content')

    <h3>{{ $host->name }}</h3>

    {{--    修改主机 --}}
    <form method="post" action="{{ route('admin.hosts.update', $host) }}">
        @csrf
        @method('PUT')

{{--        <div class="form-group">--}}
{{--            <label for="name" class="col-sm-2 col-form-label">名称</label>--}}
{{--            <input type="text" class="form-control" id="name" name="name" value="{{ $host->name }}">--}}
{{--        </div>--}}

        <div class="form-group">
            <label for="managed_price" class="col-sm-2 col-form-label">新的价格 (Drops)</label>
            <input type="text" class="form-control" id="managed_price" name="managed_price"
                   value="{{ $host->managed_price }}" placeholder="{{ $host->price }}">
            留空以使用默认价格
        </div>

        <button type="submit" class="btn btn-primary mt-3">修改</button>

    </form>


    <form method="post" action="{{ route('admin.hosts.destroy', $host) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger mt-3">删除</button>
    </form>




@endsection
