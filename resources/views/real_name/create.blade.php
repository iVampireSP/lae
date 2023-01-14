@extends('layouts.app')

@section('content')

    @php($user = auth('web')->user())

    @if ($user->real_name_verified_at)
        <x-alert-success>
            您已经完成实人认证。
        </x-alert-success>
    @else
        @if ($user->balance < 1)
            您的余额不足 1 元，无法完成实人认证。
            <br/>
            请充值后再进行实人认证。
            <hr/>
            <a href="{{ route('balances.index') }}" class="btn btn-primary">充值</a>
            <hr/>

        @endif

        <p>
            由于实人认证接口费用高昂，在实人认证成功后，我们需要收取 1 元左右的手续费。
            <br/>
            人脸识别需要使用手机摄像头，所以请使用手机浏览器进行实人认证。
        </p>

        <h3>实人认证</h3>


        {{--  if https --}}
        @if (request()->isSecure())
            <p>您的数据已加密传输。</p>
        @else
            <p class="text-danger">您的数据未加密传输，请使用 https 访问。</p>
        @endif

        <form action="{{ route('real_name.store') }}" method="post">
            @csrf
            <div class="mb-3">
                <label for="real_name" class="form-label">姓名</label>
                <input required type="text" class="form-control" id="real_name" name="real_name" placeholder="请输入您的姓名"
                       autocomplete="off" maxlength="6">
            </div>
            <div class="mb-3">
                <label for="id_card" class="form-label">身份证号</label>
                <input required type="text" class="form-control" id="id_card" name="id_card"
                       placeholder="请输入您的身份证号" autocomplete="off" maxlength="18">
            </div>
            <button type="submit" class="btn btn-primary">提交</button>
        </form>
    @endif

@endsection

