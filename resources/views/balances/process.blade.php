@extends('layouts.app')

@section('title', '支付处理')

@section('content')
    @if ($balance->paid_at)
        <h2>您已支付</h2>
        <p>我们收到了您的支付，谢谢！</p>
    @else
        <h2>正在处理</h2>

        <p>我们正在处理，您的余额很快就到账。</p>
    @endif

@endsection
