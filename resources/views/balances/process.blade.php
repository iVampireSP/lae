@extends('layouts.app')

@section('title', '支付处理')

@section('content')

    <style>
        .success-icon {
            font-size: 10rem;
            color: #096dff
        }
    </style>

    <div class="text-center align-items-center">
        @if ($balance->paid_at)
            <div class="success-icon">
                <i class="bi bi-check2-all"></i>
            </div>

            <h2>您已支付</h2>
            <p>我们收到了您的支付，谢谢！</p>
        @else

            <div class="success-icon">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <h2>正在处理</h2>

            <p>我们正在处理，您的余额很快就到账。<br/>这段时间，您可以去处理其他事情而不耽误。</p>
        @endif
    </div>

@endsection
