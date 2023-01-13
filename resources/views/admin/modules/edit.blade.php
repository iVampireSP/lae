@extends('layouts.admin')

@section('title', '模块: ' . $module->name)

@section('content')
<h3>{{ $module->name }}</h3>
<a class="mt-3" href="{{ route('admin.modules.show', $module) }}">查看</a>
<a class="mt-3" href="{{ route('admin.modules.allows', $module) }}">MQTT 授权</a>

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

    <div class="form-group mt-1">
        <label for="name">企业微信 群机器人 WebHook Key</label>
        <input type="text" class="form-control" id="wecom_key" name="wecom_key" value="{{ $module->wecom_key }}" autocomplete="off">
    </div>

    <div class="form-group mt-1">
        <label for="status">状态</label>
        <select class="form-control" id="status" name="status">
            <option value="up" @if ($module->status === 'up') selected @endif>正常</option>
            <option value="down" @if ($module->status === 'down') selected @endif>异常</option>
            <option value="maintenance" @if ($module->status === 'maintenance') selected @endif>维护模式</option>
        </select>
    </div>

    <div class="form-check mt-1">
        <input class="form-check-input" type="checkbox" value="1" id="reset_api_token" name="reset_api_token">
        <label class="form-check-label" for="reset_api_token">
            重置 Api Token(重置后，需要到对应的模块中更新，否则会导致模块无法正常工作)
        </label>
    </div>

    <button type="submit" class="btn btn-primary mt-3">提交</button>
</form>


<hr />
<form method="POST" action="{{ route('admin.modules.destroy', $module)}}" onsubmit="return confirm('删除后，业务将无法正常进行。')">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-danger">删除</button>
</form>

@endsection
