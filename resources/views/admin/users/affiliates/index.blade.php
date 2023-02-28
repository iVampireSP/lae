@extends('layouts.admin')

@section('title', '推介计划')

@section('content')
    @php($url = \Illuminate\Support\Facades\URL::route('affiliates.show', $affiliate->uuid))

    <h3>用户推介计划</h3>

    <p>
        访问量：{{ $affiliate->visits }}
    </p>
    <p>
        盈利：{{ $affiliate->revenue }} 元
    </p>

    <p>推介 URL: {{ $url }}</p>

    @php($count = $affiliateUsers->count())
    @if ($count)
        <h4>用户列表</h4>
        <table class="table table-striped">
            <thead>
            <tr>
                <th scope="col">ID</th>
                <th scope="col">用户名</th>
                <th scope="col">邮箱</th>
                <th scope="col">盈利</th>
                <th scope="col">实人状态</th>
                <th scope="col">注册时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach($affiliateUsers as $affiliateUser)
                <tr>
                    <td>{{ $affiliateUser->id }}</td>
                    <td>
                        <a href="{{ route('admin.users.edit', $affiliateUser->user_id) }}">{{ $affiliateUser->user->name }}</a>
                    </td>
                    <td>
                        <a href="{{ route('admin.users.edit', $affiliateUser->user_id) }}">{{ $affiliateUser->email }}</a>
                    </td>
                    <td>{{ $affiliateUser->revenue }} 元</td>

                    <td>
                        @if ($affiliateUser->real_name_verified_at)
                            完成
                        @else
                            <span class="text-danger">未完成</span>
                        @endif
                    </td>

                    <td>{{ $affiliateUser->created_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $affiliateUsers->links() }}
    @endif

    <h4>佣金计算方式</h4>
    <p>佣金 = 下属用户充值总额 / ({{config('settings.billing.commission_referral')}} * 100)</p>

    <h4>清除此用户的推介</h4>
    <form method="post" action="{{ route('admin.users.affiliates.destroy', [$user, $affiliate->id]) }}"
          onclick="return confirm('关联的推介数据也会被删除，但是不会扣除收益。确定清除吗？')">
        @csrf
        @method('DELETE')
        关联的推介数据也会被删除，但是不会扣除收益。
        <br/>
        <button type="submit" class="btn btn-sm btn-danger mt-1">清除</button>
    </form>

@endsection
