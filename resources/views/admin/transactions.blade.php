@extends('layouts.admin')

@section('title', '交易记录')

@section('content')
    <h2>交易记录</h2>

    <a href="?type=income">收入</a>
    <a href="?type=payout">支出</a>
    <a href="?payment=transfer">转账记录</a>

    <div class="overflow-auto">
        <table class="table table-hover">
            <thead>
            <tr>
                <th scope="col">模块</th>
                <th scope="col">支付方式</th>
                <th scope="col">说明</th>
                <th scope="col">用户 ID</th>
                <th scope="col">主机 ID</th>
                <th scope="col">金额</th>
                <th scope="col">余额</th>
                <th scope="col">交易时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($transactions as $t)
                <tr>
                    <td> &nbsp;
                        <span class="module_name" module="{{ $t->module_id }}">{{ $t->module_id }}</span>
                    </td>
                    <td>
                        <x-payment :payment="$t->payment"></x-payment>
                        <br/>
                        <a href="?payment={{ $t->payment }}">筛选</a>
                    </td>
                    <td>
                        {{ $t->description }}
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $t->user_id) }}">{{ $t->user_id }}</a>
                        <br/>
                        <a href="?user_id={{ $t->user_id }}">筛选</a>
                    </td>
                    <td>
                        @if ($t->host_id)
                            <a href="{{ route('admin.hosts.edit', $t->host_id) }}">{{ $t->host_id }}</a>
                            <br/>
                            <a href="?host_id={{ $t->host_id }}">筛选</a>
                        @endif
                    </td>

                    <td>
                        @if ($t->type === 'payout')
                            <span class="text-danger">
                            支出 {{ $t->amount }} 元
                        </span>
                        @elseif($t->type === 'income')
                            <span class="text-success">
                            收入 {{ $t->amount }} 元
                        </span>
                        @endif
                    </td>

                    <td>
                        {{ $t->user_remain ?? $t->balance }} 元
                    </td>
                    <td>
                        {{ $t->created_at }}
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

    {{ $transactions->links() }}

    <x-module-script/>
@endsection
