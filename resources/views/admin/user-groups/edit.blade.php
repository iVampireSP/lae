@extends('layouts.admin')

@section('title', '用户组: ' . $user_group->name)

@section('content')
    <h3>{{ $user_group->name }}</h3>

    <a href="{{ route('admin.user-groups.show', $user_group) }}">查看此用户组</a>

    <form method="POST" action="{{ route('admin.user-groups.update', $user_group)}}">
        @csrf
        @method('PATCH')

        <div class="form-group mt-1">
            <label for="name">名称</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ $user_group->name }}"
                   placeholder="{{ $user_group->name }}" required>
        </div>

        <div class="form-group mt-1 col-1">
            <label for="color">颜色</label>
            <input type="color" class="form-control" id="color" name="color" value="{{ $user_group->color }}" required>
        </div>

        <div class="form-group mt-1">
            <label for="discount">折扣 (%)</label>
            <input type="number" class="form-control" id="discount" name="discount" placeholder="折扣"
                   value="{{ $user_group->discount }}" required>
            {{--   提示   --}}
            <small class="form-text text-muted">折扣为 100% 时，不打折。</small>

        </div>

        <div class="form-group mt-1">
            <label for="exempt">暂停 / 终止豁免权</label>
            <select class="form-control" id="exempt" name="exempt" required>
                <option value="0">否</option>
                <option value="1" @if ($user_group->exempt) selected @endif>是</option>
            </select>
            <small class="form-text text-muted">暂停 / 终止豁免权后，用户将不再参与计费。</small>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>

    <hr/>
    <form method="POST" action="{{ route('admin.user-groups.destroy', $user_group)}}"
          onsubmit="return confirm('此用户组将不复存在。所有加入此用户组的用户将会回归默认。')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">删除</button>
    </form>

@endsection
