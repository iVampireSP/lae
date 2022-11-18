@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <h3>{{ $user->name }}</h3>
    <a href="{{ route('admin.users.show', $user) }}">切换到 {{ $user->name }}</a>


    <p>余额: {{ $user->balance }} 元, {{ $drops }} Drops</p>

    <p>注册时间: {{ $user->created_at }}</p>

    <p>邮箱: {{ $user->email }}</p>




    {{--  hosts  --}}
    <h3>主机列表</h3>
    <table class="table table-hover">
        <thead>
        <th>ID</th>
        <th>名称</th>
        <th>价格 / 5 分钟</th>
        <th>操作</th>
        </thead>
        <tbody>

        @php($drops_rage = config('drops.rate'))

        @foreach($hosts as $host)
            <tr>
                <td>{{ $host->id }}</td>
                <td>{{ $host->name }}</td>
                <td>
                    <span>{{ $host->price }} Drops</span>
                    ≈
                    <span>{{ round($host->price / $drops_rage * (30 * 24 * 60 / 5), 2) }} 元 / 月</span>

                </td>
                <td>
                    <a href="{{ route('admin.hosts.show', $host) }}" class="btn btn-primary btn-sm">查看</a>
                </td>
            </tr>
        </tbody>
        @endforeach
    </table>
    {{ $hosts->links() }}

    {{--  Work Orders  --}}
    <h3>工单列表</h3>
    <table class="table table-hover">
        <thead>
        <th>ID</th>
        <th>标题</th>
        <th>状态</th>
        <th>操作</th>
        </thead>
        <tbody>
        @foreach($workOrders as $workOrder)
            <tr>
                <td>{{ $workOrder->id }}</td>
                <td>{{ $workOrder->title }}</td>
                <td>
                    <x-work-order-status :status="$workOrder->status"/>
                </td>
                <td>
                    <a href="{{ route('admin.work-orders.show', $host) }}" class="btn btn-primary btn-sm">查看</a>
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>
    {{ $workOrders->links() }}



    <h3 class="mt-3">充值记录</h3>
    <div class="overflow-auto">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">订单号</th>
                <th scope="col">支付方式</th>
                <th scope="col">金额</th>
                <th scope="col">完成时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach($balances as $b)
                <tr>
                    <td>{{ $b->order_id }}</td>
                    <td>
                        <x-payment :payment="$b->payment"></x-payment>
                    </td>
                    <td>
                        {{ $b->amount }}
                    </td>
                    <td>
                        {{ $b->paid_at }}
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>
    </div>

    {{ $balances->links() }}


    {{--    transactions_page--}}



    {{--  账号操作  --}}
    <h3 class="mt-3">账号操作</h3>
    <form action="{{ route('admin.users.update', $user) }}" method="post">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="balance">充值余额(元)</label>
            <input type="number" class="form-control" id="balance" name="balance" placeholder="充值金额">
        </div>

        <div class="form-group">
            <label for="drops">充值 Drops</label>
            <input type="number" class="form-control" id="drops" name="drops" placeholder="充值 Drops">
        </div>

        {{-- 封禁 --}}
        <div class="form-group">
            <label for="is_banned">封禁</label>
            <select class="form-control" id="is_banned" name="is_banned">
                <option value="0">否</option>
                <option value="1" @if ($user->banned_at) selected @endif>是(将会暂停所有主机，清除所有密钥。)</option>
            </select>
        </div>

        {{-- 原因 --}}
        <div class="form-group">
            <label for="banned_reason">封禁原因</label>
            <input type="text" class="form-control" id="banned_reason" name="banned_reason" placeholder="封禁原因"
                   value="{{ $user->banned_reason }}">
        </div>

        {{-- 一次性操作 --}}
        <div class="form-group">
            <label for="one_time_action">一次性操作</label>
            <select class="form-control" id="one_time_action" name="one_time_action">
                <option value="">无</option>
                <option value="clear_all_keys">清除所有密钥</option>
                <option value="suspend_all_hosts">暂停所有主机(3天后不恢复，将会自动删除)</option>
                <option value="stop_all_hosts">停止所有主机(从暂停中恢复或者将其设置为 停止，需要用户手动启动)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>



@endsection
