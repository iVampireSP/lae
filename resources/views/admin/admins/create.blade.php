@extends('layouts.admin')

@section('title', '新建管理员')

@section('content')
    <h3>权力越大，责任越大</h3>
    <a class="mt-3" href="{{ route('admin.admins.index') }}">返回管理员列表</a>

    <form method="POST" action="{{ route('admin.admins.store')}}">
        @csrf

        <div class="form-group mt-1">
            <label for="name">用户名</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="用户名" required>
        </div>

        <div class="form-group mt-1">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" placeholder="Email" required>
        </div>

        <button type="submit" class="btn btn-primary mt-3">添加</button>
    </form>

@endsection
