@extends('layouts.app')

@section('title', '主机')

@section('content')
    <h3>主机管理</h3>
    <p>更快捷的管理计费项目。更高级的管理请前往 "仪表盘"。</p>

    <div>
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
                        <div class="dropdown">
                            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                    data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                操作
                            </button>
                            <ul class="dropdown-menu">

                                @if($host->billing_cycle)
                                    <a class="dropdown-item" href="#"
                                       onclick="return confirm('确定续费此主机？') ? document.getElementById('renew-{{$host->id}}').submit() : false;">
                                        续费此主机
                                    </a>

                                    <form action="{{ route('hosts.renew', $host) }}" id="renew-{{$host->id}}"
                                          method="post" class="d-none">
                                        @csrf
                                    </form>
                                @endif

                                @if(!$host->isRunning())
                                    <a class="dropdown-item" href="#"
                                       onclick="return confirm('确定执行此操作？') ? document.getElementById('start-{{$host->id}}').submit() : false;">
                                        启动此主机
                                    </a>

                                    <form action="{{ route('hosts.update', $host) }}" id="start-{{$host->id}}"
                                          method="post" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="running">
                                    </form>
                                @endif

                                @if(!$host->isSuspended() && !$host->isCycle())
                                    <a class="dropdown-item" href="#"
                                       onclick="return confirm('确定执行此操作？') ? document.getElementById('start-{{$host->id}}').submit() : false;">
                                        暂停此主机
                                    </a>

                                    <form action="{{ route('hosts.update', $host) }}" id="start-{{$host->id}}"
                                          method="post" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="suspended">
                                    </form>
                                @endif

                                <a class="dropdown-item" href="#"
                                   onclick="return confirm('删除操作将不可恢复，确定吗？') ? document.getElementById('delete-{{$host->id}}').submit() : false;">
                                    删除
                                </a>

                                <form action="{{ route('hosts.destroy', $host) }}" id="delete-{{$host->id}}"
                                      method="post" class="d-none">
                                    @csrf
                                    @method('DELETE')
                                </form>

                            </ul>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    {{-- 分页 --}}
    {{ $hosts->links() }}

    <br/>
    <p>还剩下周期性计费删除次数: {{ $times }}</p>

@endsection
