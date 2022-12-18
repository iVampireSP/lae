@extends('layouts.app')
@section('title', '请先完成实名认证')

@section('content')
    <h1>我们无法让您继续</h1>
    <p>您的账户尚未通过实名验证，因此无法使用此功能。</p>
    <p>请到<a href="https://www.lae.email/zh-CN/real-name-authentication">这里</a>实名验证，然后再重新登录。</p>

    <a href="{{ route('login') }}">重新登录</a>
@endsection
