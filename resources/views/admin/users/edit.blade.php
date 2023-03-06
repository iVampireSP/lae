@extends('layouts.admin')

@section('title', $user->name)

@section('content')
    <h3>{{ $user->name }}</h3>

    @if ($user->real_name_verified_at)
        <span class="text-success">实人验证于 {{ $user->real_name_verified_at }} </span>
        <br/>
    @endif

    <a href="{{ route('admin.users.show', $user) }}">作为 {{ $user->name }} 登录</a>
    <a href="{{ route('admin.transactions') }}?user_id={{ $user->id }}">有关此用户的交易记录</a>
    <a href="{{ route('admin.users.affiliates.index', $user->id) }}">推介计划</a>

    @if ($user->banned_at)
        <p class="text-danger">已被封禁，原因: {{ $user->banned_reason }}</p>
    @else
        <a href="{{  route('admin.notifications.create') }}?user_id={{  $user->id }}">给此用户发送通知</a>
    @endif


    <br/>
    <span>余额: {{ $user->balance }} 元</span> <br/>

    <span>注册时间: {{ $user->created_at }}</span> <br/>

    <span>邮箱: {{ $user->email }} @if(!$user->hasVerifiedEmail())
            <small class="text-muted">没有验证</small>
        @endif</span> <br/>


    @if ($user->birthday_at)
        <p>
            生日: {{ $user->birthday_at->format('Y-m-d') }}
            <br/>
            {{ $user->birthday_at->age }} 岁，{{ $user->isAdult() ? '已成年' : '未成年' }}。
        </p>
    @endif


    {{--  hosts  --}}
    <h3 class="mt-3">主机列表</h3>
    <table class="table table-hover">
        <thead>
        <th>ID</th>
        <th>模块</th>
        <th>名称</th>
        <th>价格 / 月</th>
        <th>状态</th>
        <th>操作</th>
        </thead>
        <tbody>

        @foreach($hosts as $host)
            <tr>
                <td>
                    {{ $host->id }}
                </td>
                <td>
                    <span class="module_name" module="{{ $host->module_id }}">{{ $host->module_id }}</span>
                </td>
                <td>{{ $host->name }}</td>
                <td>
                    <span>{{ $host->getPrice() }} 元</span>
                </td>
                <td>
                    <x-host-status :status="$host->status"/>
                </td>
                <td>
                    <a href="{{ route('admin.hosts.edit', $host) }}" class="btn btn-primary btn-sm">查看</a>
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
        <th>模块</th>
        <th>标题</th>
        <th>状态</th>
        <th>操作</th>
        </thead>
        <tbody>
        @foreach($workOrders as $workOrder)
            <tr>
                <td>{{ $workOrder->id }}</td>
                <td><span class="module_name" module="{{ $workOrder->module_id }}">{{ $workOrder->module_id }}</span>
                </td>
                <td>{{ $workOrder->title }}</td>
                <td>
                    <x-work-order-status :status="$workOrder->status"/>
                </td>
                <td>
                    <a href="{{ route('admin.work-orders.show', $workOrder) }}" class="btn btn-primary btn-sm">编辑</a>
                </td>
            </tr>
        @endforeach
        </tbody>

    </table>
    {{ $workOrders->links() }}



    <h3 class="mt-3">充值记录</h3>
    <div class="overflow-auto">
        <table class="table table-hover">
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


        <div class="form-group">
            <label for="balance">余额(元)</label>
            <input type="text" class="form-control" id="balance" name="balance" placeholder="需要在下方选择一次性操作。">
        </div>

        {{-- 一次性操作 --}}
        <div class="form-group">
            <label for="one_time_action">一次性操作</label>
            <select class="form-control" id="one_time_action" name="one_time_action">
                <option value="">无</option>
                <option value="add_balance">充值余额</option>
                <option value="reduce_balance">扣除余额</option>
                <option value="clear_all_keys">清除所有密钥</option>
                <option value="suspend_all_hosts">暂停所有主机(3天后不恢复，将会自动删除)</option>
                <option value="stop_all_hosts">停止所有主机(从暂停中恢复或者将其设置为 停止，需要用户手动启动)</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>


    {{--  用户组  --}}
    <h3 class="mt-3">用户组</h3>
    <form action="{{ route('admin.users.update', $user) }}" method="post">
        @csrf
        @method('PATCH')

        <div class="form-group">
            <label for="group_id">用户组</label>
            <select class="form-control" id="group_id" name="user_group_id">
                <option value="">无</option>
                @foreach($groups as $group)
                    <option value="{{ $group->id }}"
                            @if ($user->user_group_id == $group->id) selected @endif>{{ $group->name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn btn-primary mt-3">提交</button>
    </form>


    <h3 class="mt-3">实人认证信息</h3>
    <p>
        请注意自己的底线，不要随意改写及泄漏以下信息。
    </p>
    <div id="real_name_form">
        <form action="{{ route('admin.users.update', $user) }}" method="post">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="real_name">姓名</label>
                <input type="text" class="form-control" id="real_name" name="real_name" placeholder="姓名"
                       value="{{ $user->real_name }}" autocomplete="off">
            </div>

            <div class="form-group">
                <label for="id_card">身份证号</label>
                <input type="text" class="form-control" id="id_card" name="id_card" placeholder="身份证号"
                       value="{{ $user->id_card }}" maxlength="18" autocomplete="off">
            </div>

            <button type="submit" class="btn btn-primary mt-3">提交</button>
        </form>
    </div>

    <h3 class="mt-4">删除用户</h3>
    <p>
        这是个非常危险的操作，请三思而后行。
    </p>
    <form action="{{ route('admin.users.destroy', $user) }}" method="post">
        @csrf
        @method('DELETE')

        <button type="submit" class="btn btn-danger mt-3" onclick="return confirm('请再次确认要删除此用户吗？')">删除
        </button>
    </form>


    <style>
        #real_name_form {
            filter: blur(10px);
            transition: all 0.5s;
        }

        #real_name_form:hover {
            filter: blur(0);
        }
    </style>

@endsection
