@extends('layouts.app')

@section('title', '订阅')

@section('content')
    <h3>订阅</h3>

    <div>
        <table class="table table-hover">
            <thead>
            <th>ID</th>
            <th>名称</th>
            <th>模块</th>
            <th>计划 ID</th>
            <th>续期价格</th>
            <th>状态</th>
            <th>到期时间</th>
            <th>操作</th>
            </thead>

            <tbody>
            @foreach ($subscriptions as $subscription)
                <tr>
                    <td>
                        {{ $subscription->id }}
                    </td>
                    <td>
                        {{ $subscription->name }}
                    </td>
                    <td>
                        {{ $subscription->module->name }}
                    </td>
                    <td>
                        {{ $subscription->plan_id }}
                    </td>
                    <td class="small">
                        {{ $subscription->price }} 元
                    </td>
                    <td>
                        <x-host-status :status="$subscription->status"/>
                        @if ($subscription->cancel_at_period_end)
                            <br/>
                            <small>
                                <span class="text-danger">自动续订已取消</span>
                            </small>
                        @endif
                    </td>
                    <td>
                        <span class="small">
                            @if ($subscription->isTrial())
                                {{ $subscription->trial_ends_at }}(试用)
                            @else
                                {{ $subscription->expired_at }}
                            @endif
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
                                @if ($subscription->isDraft())
                                    <a class="dropdown-item active"
                                       href="{{ route('subscriptions.show', $subscription) }}">
                                        开始订阅
                                    </a>
                                @endif

                                @if ($subscription->isActive())
                                    <a class="dropdown-item" href="#"
                                       onclick="document.getElementById('update-{{$subscription->id}}').submit()">
                                        {{ $subscription->cancel_at_period_end ? '启用自动续订' : '取消自动续订'}}
                                    </a>

                                    <form action="{{ route('subscriptions.update', $subscription) }}"
                                          id="update-{{$subscription->id}}"
                                          method="post" class="d-none">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="cancel_at_period_end"
                                               value="{{ !$subscription->cancel_at_period_end ? '1' : '0'}}">
                                    </form>
                                @endif

                                <a class="dropdown-item" href="#"
                                   onclick="return confirm('删除操作将不可恢复，确定吗？') ? document.getElementById('delete-{{$subscription->id}}').submit() : false;">
                                    删除订阅
                                </a>

                                <form action="{{ route('subscriptions.destroy', $subscription) }}"
                                      id="delete-{{$subscription->id}}"
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

@endsection
