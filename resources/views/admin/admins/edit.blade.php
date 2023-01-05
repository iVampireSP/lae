@extends('layouts.admin')

@section('title', '管理员: ' . $admin->name)

@section('content')
    <h3>{{ $admin->name }}</h3>

    <form method="POST" action="{{ route('admin.admins.update', $admin)}}">
        @csrf
        @method('PATCH')

        <div class="form-group mt-1">
            <label for="name">用户名</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $admin->name }}" required>
        </div>

        <div class="form-group mt-1">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" name="email" value="{{ $admin->email }}">
        </div>

        <div class="form-check mt-1">
            <input class="form-check-input" type="checkbox" value="1" id="reset_password" name="reset_password">
            <label class="form-check-label" for="reset_password">
                重置密码
            </label>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>


    <hr/>
    <form method="POST" action="{{ route('admin.admins.destroy', $admin)}}"
          onsubmit="return confirm('此管理员将不复存在。')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">删除</button>
    </form>

@endsection
