@extends('layouts.admin')

@section('title', '模块:' . $module->name)

@section('content')
    <h3>{{ $module->name }}</h3>
    <a class="mt-3" href="{{ route('admin.modules.show', $module) }}">查看</a>

    <form method="POST" action="{{ route('admin.modules.update', $module)}}">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="name">ID (修改后，路由也会改变)</label>
            <input type="text" class="form-control" id="id" name="id" value="{{ $module->id }}">
        </div>

        <div class="form-group">
            <label for="name">名称</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $module->name }}">
        </div>

        <div class="form-group mt-1">
            <label for="name">对端地址</label>
            <input type="text" class="form-control" id="url" name="url" value="{{ $module->url }}">
        </div>

        <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" value="1" id="reset_api_token" name="reset_api_token">
            <label class="form-check-label" for="reset_api_token">
                重置 Api Token(重置后，需要到对应的模块中更新，否则会导致模块无法正常工作)
            </label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>


    <hr/>
    <form method="POST" action="{{ route('admin.modules.destroy', $module)}}"
          onsubmit="return confirm('删除后，业务将无法正常进行。')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">删除</button>
    </form>

@endsection
