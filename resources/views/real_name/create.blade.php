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

        <x-alert-info>
            您也可以到 <a target="_blank" href="{{ config('oauth.oauth_domain') }}">{{ config('oauth.oauth_name') }}</a>
            中实名认证。重新登录后即可同步。
        </x-alert-info>

        <x-alert-info>
            实人认证产品是结合公安一所“互联网+”可信身份认证平台（简称CTID平台），通过用户活体视频进行活体检测得到人脸视频，通过OCR扫描用户身份证获取姓名+身份证号，并将人脸视频检测成功后获取的高质量人像照片直连公安一所“互联网+可信身份认证平台”（简称CTID平台）进行照片及信息比对，返回权威比对结果。H5全流程，接入简单，应用方便快捷。
        </x-alert-info>
        <x-alert-warning>
            莱云 隐私协议和 TOS: <a target="_blank"
                                    href="https://www.laecloud.com/tos/">https://www.laecloud.com/tos</a>
            <br/>
            公安 CTID 实人认证服务由 北京一砂信息技术有限公司 提供。它将会引导您完成实人认证。
        </x-alert-warning>
        <x-alert-success>
            为监管需要，我们会加密保存您的身份数据，不会泄露给任何第三方。更详细的隐私政策请查看上方的链接中的
            "隐私及个人信息的保护"。
        </x-alert-success>
        <x-alert-warning>
            如果您是怀抱志向的未成年人，请确保您的父母或监护人已经同意您进行实人认证。
            <br/>
            但是请注意，如果您的父母或监护人不同意您进行实人认证，我们将无法为您提供服务。
        </x-alert-warning>
        <x-alert-warning>
            实人认证的人脸数据来自 "互联网+”可信身份认证平台"，我们不会保存您的人脸数据。
            <br/>
            如果您未办理过身份证，则公安数据库中没有您的人脸信息，请勿进行实人认证。
        </x-alert-warning>
        <x-alert-warning>
            您的年龄必须大于 {{ config('settings.supports.real_name.min_age') }}
            岁，小于 {{ config('settings.supports.real_name.max_age') }} 岁，否则无法进行实人认证。
        </x-alert-warning>

        <h3>实人认证</h3>


        {{--  if https --}}
        @if (request()->isSecure())
            <p>实名认证数据将全部加密传输，请放心实名。</p>
        @else
            <p class="text-danger">您的数据未加密传输，请使用 HTTPS 访问。</p>
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

