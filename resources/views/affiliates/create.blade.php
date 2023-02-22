@extends('layouts.app')

@section('title', '推介计划')

@section('content')

    <h3>加入推介计划</h3>
    <p>
        让更多人用上我们的产品，您将从他们实名认证成功之后的每笔充值中获取 {{ config('settings.billing.commission_referral') * 100 . '%' }}
        的佣金。</p>

    @if ($user->affiliate_id)
        <span>您被 {{ $user->affiliateUser->affiliate->user->name }}#{{ $user->affiliateUser->affiliate->user_id }} 引荐。</span>
        @if ($user->affiliateUser->affiliate->revenue > 5)
            <span>您的推介人已经获得了 {{ $user->affiliateUser->affiliate->revenue }} 元的佣金。</span>
        @endif
    @endif

    <form class="mt-3" method="post" action="{{ route('affiliates.store') }}">
        @csrf
        <button type="submit" class="btn btn-primary">加入推介计划</button>
    </form>

@endsection
