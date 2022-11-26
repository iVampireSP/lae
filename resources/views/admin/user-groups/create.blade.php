@extends('layouts.admin')

@section('title', '新建用户组')

@section('content')
    <h3>新建用户组</h3>

    <a class="mt-3" href="{{ route('admin.user-groups.index') }}">返回 用户组列表</a>

    <form method="POST" action="{{ route('admin.user-groups.store')}}">
        @csrf

        <div class="form-group mt-1">
            <label for="name">名称</label>
            <input type="text" class="form-control" id="name" name="name" placeholder="名称" required>
        </div>

        <div class="form-group mt-1 col-1">
            <label for="color">颜色</label>
            <input type="color" class="form-control" id="color" name="color" required>
        </div>

        <div class="form-group mt-1">
            <label for="discount">折扣 (%)</label>
            <input type="number" class="form-control" id="discount" name="discount" placeholder="折扣" value="100"
                   required>
            {{--   提示   --}}
            <small class="form-text text-muted">折扣为 100% 时，不打折。</small>

        </div>

        <div class="form-group mt-1">
            <label for="exempt">暂停 / 终止豁免权</label>
            <select class="form-control" id="exempt" name="exempt" required>
                <option value="0">否</option>
                <option value="1">是</option>
            </select>
            <small class="form-text text-muted">暂停 / 终止豁免权后，用户将不再参与计费。</small>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>

@endsection
