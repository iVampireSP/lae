@extends('layouts.app')

@section('title', '推介计划')

@section('content')
    @php($url = \Illuminate\Support\Facades\URL::route('affiliates.show', $affiliate->uuid))

    <h3>推介计划</h3>

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
                <th scope="col">盈利</th>
                {{--                <th scope="col">实人状态</th>--}}
                <th scope="col">注册时间</th>
            </tr>
            </thead>
            <tbody>
            @foreach($affiliateUsers as $user)
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->user->name }}</td>
                    <td>{{ $user->revenue }} 元</td>

                    {{--                    <td>--}}
                    {{--                        @if ($user->real_name_verified_at)--}}
                    {{--                            完成--}}
                    {{--                        @else--}}
                    {{--                            <span class="text-danger">未完成</span>--}}
                    {{--                        @endif--}}
                    {{--                    </td>--}}

                    <td>{{ $user->created_at }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $affiliateUsers->links() }}
    @endif

    <h4>广告词</h4>
    <p>您可以定义一个广告词来收获更多的用户。让新用户访问您的推介 URL 并完成注册，您就会获得一个下属用户。</p>

    <h4>金额到账时间</h4>
    <p>
        下属用户充值所获得的佣金将会立即到您的余额。
    </p>

    <h4>佣金计算方式</h4>
    <p>佣金 = 下属用户充值总额 / ({{config('settings.billing.commission_referral')}} * 100)</p>

    <h4>离开推介计划</h4>
    <form method="post" action="{{ route('affiliates.destroy', $affiliate->id) }}"
          onclick="return confirm('离开除后将不会获得收益，关联的推介数据也会被删除。')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">离开</button>
    </form>

@endsection
