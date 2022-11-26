@extends('layouts.admin')

@section('title', '用户组')

@section('content')

    <h3>用户组</h3>
    <p>将用户划分到一个组中，可让他们享受到特别待遇。</p>

    <a href="{{ route('admin.user-groups.create') }}">新用户组</a>
    <div class="overflow-auto mt-3">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>名称</th>
            <th>折扣</th>
            <th>暂停 / 终止豁免权</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($user_groups as $user_group)
                <tr>
                    <td>
                        <a href="{{ route('admin.user-groups.edit', $user_group) }}">
                            {{ $user_group->id }}
                        </a>
                    </td>
                    <td>
                        <span class="badge badge-pill" style="background-color: {{ $user_group->color }}">&nbsp;</span>
                        &nbsp;
                        {{ $user_group->name }}
                    </td>
                    <td>
                        @if ($user_group->discount == 100)
                            不享受折扣
                        @else
                            享受 {{ $user_group->discount }}% 的折扣
                        @endif
                    </td>
                    <td>
                        {{ $user_group->exempt ? '是' : '否' }}
                    </td>
                    <td>
                        <a href="{{ route('admin.user-groups.show', $user_group) }}"
                           class="btn btn-primary btn-sm">查看</a>

                        <a href="{{ route('admin.user-groups.edit', $user_group) }}"
                           class="btn btn-primary btn-sm">编辑</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $user_groups->links() }}

@endsection
