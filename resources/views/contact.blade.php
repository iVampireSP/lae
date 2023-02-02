@extends('layouts.app')

@section('content')

    <p>请注意，我们唯一的官方控制面板是
        <a target="_blank"
           href="{{ config('settings.dashboard.base_url') }}">{{ config('settings.dashboard.base_url') }}</a>。
        <br/>
        官方没有推出过任何其它形式的客户端（比如桌面客户端等）。我们不会其它形式的客户端做出任何技术支持，也不解答任何问题，也不负责任何损失。
    </p>

    @auth
        <p>你好， {{ auth()->user()->name }}。
            <br/>
            如果您在使用一些服务方面遇到了问题，可以在"仪表盘"的菜单中的"工单"，联系我们。
            <br/>
            "工单"是我们的客户支持系统，您可以在这里提交工单，我们会尽快处理。
            <br/>
            "工单"会根据您的服务，将您的工单投递到不同的部门，交给他们进行处理。
        </p>

    @endauth

    <p>
        您可以加入我们的 QQ 群: 769779712。
    </p>

@endsection
