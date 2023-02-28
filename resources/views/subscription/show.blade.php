@extends('layouts.app')

@section('title', $subscription->name)

@section('content')
    @if (!$subscription->canActivate(true))
        <div class="d-flex justify-content-center align-items-center" style="height: 85vh">
            <div class="text-center">
                <h2>不能激活此订阅。</h2>
                <br />
                <p>
                    模块向您发送了一个不正确的订阅草稿。
                </p>
            </div>
        </div>
    @elseif ($subscription->isDraft())
        <div class="d-flex justify-content-center align-items-center" style="height: 85vh">
            <div>
                <h2 class="text-center">激活此订阅</h2>
                <table class="table table-bordered table-striped">
                    <tr>
                        <td>模块</td>
                        <td>{{ $subscription->module->name }}</td>
                    </tr>
                    <tr>
                        <td>月付价格</td>
                        <td>{{ $subscription->price }}</td>
                    </tr>
                    <tr>
                        <td>计划 ID</td>
                        <td>{{ $subscription->plan_id }}</td>
                    </tr>
                    <tr>
                        <td>截止</td>
                        <td>
                            {{ ($subscription->expired_at ?? $subscription->trial_ends_at) ?? '订阅后开始计算' }}
                            @if ($subscription->isTrial())
                                <span class="badge badge-success">试用</span>
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="text-center">
                    <form action="{{ route('subscriptions.update', $subscription) }}" method="post">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="active"/>

                        <button type="submit" class="btn btn-primary btn-sm btn-block">激活</button>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($subscription->isActive())
        <div class="d-flex justify-content-center align-items-center" style="height: 85vh">
            <div>
                <h2 class="text-center">谢谢。</h2>
            </div>
        </div>
    @endif
@endsection
