@extends('layouts.admin')

@section('title', '模块订阅授权')

@section('content')
    <h3>授权</h3>
    <p>允许多个模块之间互相发布消息。</p>
    <a href="{{ route('admin.modules.show', $module) }}">查看</a>
    <a href="{{ route('admin.modules.edit', $module) }}">编辑</a>

    <div class="overflow-auto mt-3">
        <table class="table table-hover">
            <thead>
            <th>模块 ID</th>
            <th>显示名称</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($allows as $allow)
                <tr>
                    <td>
                        {{ $allow->allowed_module_id }}
                    </td>

                    <td>
                        {{ $allow->allowed_module->name }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.modules.allows.destroy', [$module, $allow]) }}">
                            @method('delete')
                            @csrf
                            <button type="submit" class="btn btn-danger btn-sm">删除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <form action="{{ route('admin.modules.allows.store', $module) }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="allowed_module_id">另一个 模块</label>

            <select name="allowed_module_id" id="allowed_module_id" class="form-control">
                <option value="">无</option>
                @foreach ($modules as $module)
                    <option value="{{ $module->id }}">{{ $module->name }}</option>
                @endforeach
            </select>
        </div>


        <div class="form-group mt-3">
            <button type="submit" class="btn btn-primary">提交</button>
        </div>
    </form>
@endsection
