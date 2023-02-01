@extends('layouts.admin')

@section('title', '主机:' . $host->name)

@section('content')

    <h3>{{ $host->name }}</h3>

    {{--    修改主机 --}}
    <form method="post" action="{{ route('admin.hosts.update', $host) }}">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="name" class="col-sm-2 col-form-label">名称</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $host->name }}">
        </div>

        <div class="form-group">
            <label for="managed_price" class="col-sm-2 col-form-label">新的价格 (元)</label>
            <input type="text" class="form-control" id="managed_price" name="managed_price"
                   value="{{ $host->managed_price }}" placeholder="{{ $host->price }}">
            留空以使用默认价格
        </div>

        <div class="form-group">
            <label for="status" class="col-sm-2 col-form-label">状态</label>
            <select class="form-control" id="status" name="status">
                <option value="running" {{ $host->status == 'running' ? 'selected' : '' }}>运行中</option>
                <option value="stopped" {{ $host->status == 'stopped' ? 'selected' : '' }}>已停止</option>
                <option value="suspended" {{ $host->status == 'suspended' ? 'selected' : '' }}>已暂停</option>
                <option value="error" {{ $host->status == 'error' ? 'selected' : '' }}>错误 (提交此项目将会被忽略)
                </option>
                <option value="error" {{ $host->status == 'pending' ? 'selected' : '' }}>等待中 (提交此项目将会被忽略)
                </option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">修改</button>

    </form>

    <hr/>

    <form method="post" action="{{ route('admin.hosts.refresh', $host) }}">
        @csrf
        <button type="submit" class="btn btn-primary mt-3">刷新此主机</button>
    </form>

    <hr/>

    <form method="post" action="{{ route('admin.hosts.destroy', $host) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger mt-3">删除</button>
    </form>

@endsection
