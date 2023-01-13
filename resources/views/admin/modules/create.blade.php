@extends('layouts.admin')

@section('title', '新建模块')

@section('content')
<h3>新建模块</h3>

<form method="POST" action="{{ route('admin.modules.store')}}">
    @csrf

    <div class="form-group">
        <label for="name">ID</label>
        <input type="text" class="form-control" id="id" name="id">
    </div>

    <div class="form-group">
        <label for="name">名称</label>
        <input type="text" class="form-control" id="name" name="name">
    </div>

    <div class="form-group mt-1">
        <label for="name">对端地址</label>
        <input type="text" class="form-control" id="url" name="url">
    </div>

    <div class="form-group mt-1">
        <label for="name">企业微信 群机器人 WebHook Key</label>
        <input type="text" class="form-control" id="wecom_key" name="wecom_key">
    </div>

    <!-- 选择状态 -->
    <div class="form-group mt-1">
        <label for="status">状态</label>
        <select class="form-control" id="status" name="status">
            <option value="up">正常</option>
            <option value="down">异常</option>
            <option value="maintenance">维护模式</option>
        </select>
    </div>

    <button type="submit" class="btn btn-primary mt-3">提交</button>
</form>

@endsection
