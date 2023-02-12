@extends('layouts.app')

@section('title', '主机')

@section('content')
    <h3>主机管理</h3>
    <p>更快捷的管理计费项目。更高级的管理请前往 "仪表盘"。</p>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>模块</th>
            <th>名称</th>
            <th>月估算价格</th>
            <th>状态</th>
            <th>更新 / 创建 时间</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($hosts as $host)
                <tr>
                    <td>
                        {{ $host->id }}
                    </td>
                    <td>
                        <a>{{ $host->module->name }}</a>
                    </td>
                    <td>
                        {{ $host->name }}
                    </td>

                    <td>
                        @if ($host->managed_price !== null)
                            <span class="text-danger">{{ $host->managed_price }} 元</span>
                        @else
                            {{ $host->price }} 元
                        @endif
                        <br/>
                        @if($host->billing_cycle)
                            <x-billing-cycle :cycle="$host->billing_cycle"/>
                            到期时间：{{ $host->next_due_at }}
                        @endif
                    </td>
                    <td>
                        <x-host-status :status="$host->status"/>
                    </td>
                    <td>
                        <span class="small">
                            {{ $host->updated_at }}
                            <br/>
                            {{ $host->created_at }}
                        </span>
                    </td>
                    <td>
                        <form action="{{ route('hosts.destroy', $host) }}" method="post" class="d-inline">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="btn btn-sm btn-danger"
                                    onclick="return confirm('删除后，数据将无法找回，也不可回滚更改。')">删除
                            </button>
                        </form>

                        @if($host->billing_cycle)
                            <form action="{{ route('hosts.renew', $host) }}" method="post" class="d-inline">
                                @csrf

                                <button type="submit" class="btn btn-sm btn-primary"
                                        onclick="return confirm('将续费此主机。')">续费
                                </button>
                            </form>
                        @endif


                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $hosts->links() }}

@endsection
