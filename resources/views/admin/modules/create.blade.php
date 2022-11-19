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

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>

@endsection
