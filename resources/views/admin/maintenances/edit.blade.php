@extends('layouts.admin')

@section('title', '编辑: ' . $maintenance->name)

@section('content')
    <h3>{{ $maintenance->name }}</h3>
    <a class="mt-3" href="{{ route('admin.maintenances.index') }}">返回计划列表</a>

    <form method="POST" action="{{ route('admin.maintenances.update', $maintenance) }}">
        @csrf
        @method('PATCH')

        <div class="form-group mt-1">
            <label for="name">维护名称</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="维护名称" value={{ $maintenance->name }} required>
        </div>

        <div class="form-group mt-1">
            <label for="content">维护内容</label>
            <textarea class="form-control" id="content" name="content" placeholder="维护内容" required>{{ $maintenance->content }}</textarea>
        </div>

        {{-- 模块 ID --}}
        <div class="form-group mt-1">
            <label for="module_id">模块</label>
            <select class="form-control" id="module_id" name="module_id">
                <option value="">无</option>
                @foreach ($modules as $m)
                    <option value="{{ $m->id }}" @if($maintenance->module_id === $m->id) selected @endif>{{ $m->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group mt-1">
            <label for="start_at">开始时间</label>
            <input type="datetime-local" class="form-control" id="start_at" name="start_at" value="{{ $maintenance->start_at }}" placeholder="开始时间">
        </div>

        <div class="form-group mt-1">
            <label for="end_at">结束时间</label>
            <input type="datetime-local" class="form-control" id="end_at" name="end_at" value="{{ $maintenance->end_at }}" placeholder="结束时间">
        </div>

        <button type="submit" class="btn btn-primary mt-3">更新</button>
    </form>

    <hr />
    <h4>删除计划</h4>

    <form method="POST" action="{{ route('admin.maintenances.destroy', $maintenance) }}">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-danger mt-3">删除</button>
    </form>
@endsection
