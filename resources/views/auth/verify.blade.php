@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-center align-items-center h-screen" style="height: 80vh">
        <div class="text-center">
            @if (session('resent'))
                <span style="font-size: 10rem">
                    <i class="bi bi-envelope-check"></i>
                </span>
                <h5>{{ __('A fresh verification link has been sent to your email address.') }}</h5>
            @else
                <span style="font-size: 10rem">
                    <i class="bi bi-envelope-at"></i>
                </span>
                <h3>验证电子邮件地址</h3>
                {{ __('Before proceeding, please check your email for a verification link.') }}
                {{ __('If you did not receive the email') }},
                <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                    @csrf
                    <button type="submit"
                            class="btn btn-link p-0 m-0 align-baseline">{{ __('click here to request another') }}</button>
                    。
                </form>

                <p>我们将会删除超过 3 天没有验证邮箱的用户。</p>
            @endif

        </div>
    </div>

@endsection
